<?php

class Interpreter {
    private $program;
    private $source_code;
    public $variables = [];
    public $unoptimised_program;
    public $filepath;
    public $cursor;
    public $diagnostic;
    public $current_dir;
    private $internal_buffer = [];

    public function __construct($source_code, $filepath) {
        $this->source_code = $source_code;
        $this->filepath =  realpath($filepath);
        $this->current_dir = dirname($this->filepath);
        $lexer = new Lexer($this->source_code, $this->filepath);
        $parser = new Parser($lexer);
        $program = $parser->parse();
        $optimizer = new Optimizer($program);
        $this->unoptimised_program = clone $optimizer->program;
        $this->program = $optimizer->optimize();
        $this->cursor = new Cursor($this->source_code, $this->filepath);
        $this->diagnostic = new Diagnostic();
    }

    public function interpret() {
        foreach ($this->program->body as $statement) {
            $this->execute_statement($statement);
            if (isset($statement->pos)) {
                $this->cursor->goto($statement->pos[1]);
            }
        }
        return implode('',$this->internal_buffer);
    }

    public function execute_statement($statement) {
        if ($statement instanceof Kill) {
            die;
        } elseif ($statement instanceof DeclareVariable) {
            if (array_key_exists($statement->name, $this->variables)) {
                $this->diagnostic->raise(ErrorType::Runtime, "Identifier \"" . $statement->name . "\" already exists.", $statement->pos[0], $this->cursor);
            }
            $this->variables[$statement->name] = $this->evaluate_expression($statement->value);
        } elseif ($statement instanceof EchoStatement) {
            $value = $this->evaluate_expression($statement->expression);
            $buffered = "";
            if (is_array($value)) {
                $buffered .= '[';
                $i = 0;
                foreach ($value as $val) {
                    $buffered .= "$val";
                    $i++;
                    if ($i < count($value)) {
                        $buffered .= ", ";
                    }
                }
                $buffered .= ']';
            } elseif (is_object($value)) {
                $buffered .= "<" . get_class($value) . " '{$value->name->value}' >"; 
            } else {
                $buffered .= "{$value}";
            }
            $buffered = str_replace('\n', PHP_EOL, $buffered);
            $this->internal_buffer[] = $buffered;
        }
        elseif ($statement instanceof LiteralText) {
            $this->internal_buffer[] = $statement->value;
        }

        elseif ($statement instanceof ObjectDeclare) {
            $name = $statement->name;

            $old_vars = $this->variables;
            $new_vars = [];
            foreach ($statement->body as $statement_child) {
                if ($statement_child instanceof DeclareFunction || $statement_child instanceof DeclareVariable) {
                    $this->execute_statement($statement_child);
                } else {
                    $this->diagnostic->raise(ErrorType::Syntax, "Invalid statement in object declaration, got: " . get_class($statement_child), $statement->pos[0], $this->cursor);
                }
            }
            $properties = array_diff_key($this->variables, $old_vars);
            foreach ($properties as $key => $value) {
                unset($this->variables[$key]);
            }

            $this->variables[$statement->name->value] = new ObjectType($name, $properties, $this->cursor);
        }
        elseif ($statement instanceof LoopStatement) {
                while ($this->evaluate_expression($statement->condition)) {
                    foreach ($statement->body as $statement_child) {
                        if ($statement_child instanceof BreakLoop) {
                            break;
                        } elseif ($statement_child instanceof ContinueLoop) {
                            continue;
                        } else {
                            $this->execute_statement($statement_child);
                        }
                    }
                }
        } 
        elseif ($statement instanceof AssignVariable) {
        if ($statement->left instanceof Identifier) {
                $name = $statement->left->value;
                if (!isset($this->variables[$name])) {
                    $this->diagnostic->raise(ErrorType::Identifier, "Name $name is not defined.", $statement->pos[0], $this->cursor);
                }
                $this->variables[$name] = $this->evaluate_expression($statement->value);
                
            } elseif ($statement->left instanceof MemberAccess) {
                $object = $this->evaluate_expression($statement->left->object);
                $name = $this->evaluate_expression($statement->left->property);
                $this->variables[$statement->left->object->value][$statement->left->property->value] = $this->evaluate_expression($statement->value);
            } elseif ($statement->left instanceof Subscript && $statement->left->slice instanceof Index) {
                $left = $this->evaluate_expression($statement->left->identifier);
                $index = $this->evaluate_expression($statement->left->slice->value);
                $this->variables[$left][$index] = $this->evaluate_expression($statement->value);
            } else {
                $this->diagnostic->raise(ErrorType::Syntax, "Invalid assignment.", $statement->pos[0], $this->cursor);
            }
        }
        elseif ($statement instanceof AugAssign) {
            $name = $statement->name;
            if (!isset($this->variables[$name])) {
                $this->diagnostic->raise(ErrorType::Identifier, "Identifier \"" . $name . "\" is not defined.", $statement->pos[0], $this->cursor);
            }
            $this->variables[$name] = $this->evaluate_expression(new BinaryOperation(new Identifier($statement->name, $statement->pos), $statement->operator, $statement->value, $statement->pos));
        } elseif ($statement instanceof BreakLoop) {
            $this->diagnostic->raise(ErrorType::Runtime, "Use of continue break of a loop is illegal.", $statement->pos[0], $this->cursor);
        } elseif ($statement instanceof ContinueLoop) {
            $this->diagnostic->raise(ErrorType::Runtime, "Use of continue outside of a loop is illegal.", $statement->pos[0], $this->cursor);
        } elseif ($statement instanceof IncludeStatement) {
            $source = file_get_contents($this->current_dir . '/' . $this->evaluate_expression($statement->filepath)) or die('Failed to open file: ' . $this->current_dir . '/' . $this->evaluate_expression($statement->filepath));
            $inside_interpreter = new Interpreter($source, $this->current_dir . '/' . $this->evaluate_expression($statement->filepath));
            $this->internal_buffer[] = $inside_interpreter->interpret();
        }  elseif ($statement instanceof IfStatement) {
            $condition = $this->evaluate_expression($statement->condition);
            if ($condition) {
                foreach ($statement->body as $statement_child) {
                    $this->execute_statement($statement_child);
                }
            } else {
                $pass_to_else = true;
                foreach ($statement->tryother as $elif) {
                    if ($this->evaluate_expression($elif->condition)) {
                        foreach ($elif->body as $statement_child) {
                            $this->execute_statement($statement_child);
                        }
                        $pass_to_else = false;
                        break;
                    }
                }
                if ($pass_to_else && isset($statement->else)) {
                    foreach ($statement->else as $statement_child) {
                        $this->execute_statement($statement_child);
                    }
                }
            }
        } elseif ($statement instanceof DeleteVariable) {
            $name = $statement->name;
            if (!isset($this->variables[$name])) {
                $this->diagnostic->raise(ErrorType::Identifier, "Variable \"" . $name . "\" was never defined.", $statement->pos[0], $this->cursor);
            }
            unset($this->variables[$name]);
        } elseif ($statement instanceof DeclareFunction) {
            $name = $statement->name;
            $args = $statement->args;
            $body = $statement->body;

            $this->variables[$name->value] = new FunctionObject($name, $args, $body, [$this->variables, []], $statement->pos);

        } else {
            $this->evaluate_expression($statement);
        }
    }

    public function evaluate_expression($expression) {
        if ($expression instanceof ImportStatement) {
            $file = $this->evaluate_expression($expression->filepath);
            if (!str_ends_with($file, '.lumen')) {
                $file = $file . '.lumen';
            }
            if (!file_exists($this->current_dir . '/' . $file)) {
                $this->diagnostic->raise(ErrorType::Import, "No such module was found at: " . $this->current_dir . '/' . $file . "", $expression->pos[0], $this->cursor);
            }
            $source = file_get_contents($this->current_dir . '/' . $file) or die('Failed to open file: ' . $this->current_dir . '/' . $file);
            $inside_interpreter = new Interpreter($source, $this->current_dir . '/' . $file);
            $optimized = &$inside_interpreter->program;

            $last_literaltext_key = null;
            foreach (($optimized->body) as $key => $value) {
                if ($value instanceof LiteralText) {
                    $last_literaltext_key = $key;
                }
            }
            $inside_interpreter->program->body = array_slice($optimized->body, 0, $last_literaltext_key + 1);
            $inside_interpreter->interpret();
            $new_import_object = new ObjectType("import-" . md5($file), $inside_interpreter->variables, $inside_interpreter);
            return $new_import_object;
        }

        if ($expression instanceof NumberLiteral) {
            return (float)$expression->value;
        } if ($expression instanceof None) {
            return '';
        } 
        if ($expression instanceof StringLiteral) {
            return $expression->value;
        } 
        if ($expression instanceof FunctionCall) {
            $nativeLib = new NativeLib();
            if ($nativeLib->has($expression->name)) {
                $args = [];
                foreach ($expression->args as $arg) {
                    array_push($args, $this->evaluate_expression($arg));
                }
                return call_user_func_array([$nativeLib, $expression->name], $args);
            } 
    
            if ($this->variables[$expression->name] instanceof ObjectType) {
                return $this->call_object($expression);
            }
            if (!array_key_exists($expression->name, $this->variables)) {
                $this->diagnostic->raise(ErrorType::Runtime, "Undefined name \"" . $expression->name . "\" was called.", $expression->pos[0], $this->cursor);
            }
            return $this->call_function($this->variables[$expression->name], $expression->args);
        }
        if ($expression instanceof MemberAccess) {

            $object = $this->evaluate_expression($expression->object);

            if ($expression->property instanceof FunctionCall) {
                $fn = $object->properties[$expression->property->name];
                $args = $expression->property->args;
                if (substr( $object->name, 0, 7 ) === "import-") {
                    return $object->interpreter->call_function($fn, $args);
                }
                $props = null;
                $obj_props = [];
                if (count($expression->property->args) > 0 && $expression->property->args[0]->value == 'self') {
                    if (!is_null($object->properties)) {
                        $obj_props = &$object->properties;
                    }
                    $args = array_merge([&$obj_props], $expression->property->args);
                    $props = &$object->properties;
                }
                return $this->call_function($fn, $args, TRUE, $props);
            } 
            if (($object instanceof ObjectType)) {
                if (!isset($object->properties[$expression->property->value])) {
                    $this->diagnostic->raise(ErrorType::Identifier, "Undefined property \"" . $expression->property->value . "\" was called.", $expression->pos[0], $this->cursor);
                }
                return $object->properties[$expression->property->value];
            } else {
                return $object[$expression->property->value];

            }
        }

        if ($expression instanceof Identifier) {
            if (!array_key_exists($expression->value, $this->variables)) {
                $this->diagnostic->raise(ErrorType::Identifier, "Undefined name \"" . $expression->value . "\" was called.", $expression->pos[0], $this->cursor);
            }

            return $this->variables[$expression->value];
        }
        if ($expression instanceof BoolOp) {
            $left = $this->evaluate_expression($expression->left);
            $right = $this->evaluate_expression($expression->right);
            switch ($expression->operator) {
                case BoolOperand::And:
                    return $right && $left;
                case BoolOperand::Or:
                    return $right || $left;
                }
        }
        if ($expression instanceof BinaryOperation) {
            $left = $this->evaluate_expression($expression->left);
            $right = $this->evaluate_expression($expression->right);
            switch ($expression->operator) {
                case Operation::Add:
                    if (is_string($left) || is_string($right)) {
                        return "$right" . "$left";
                    }
                    return $right + $left;
                case Operation::Sub:
                    return $right - $left;
                case Operation::Div:
                    return $right / $left;
                case Operation::Mult:
                    return $right * $left;
                case Operation::Modulo:
                    if (($right - floatval(intval($right)) == 0) && ($left - floatval(intval($left)) == 0)) {
                        return floatval(intval($right) % intval($left));
                    }
                    $this->diagnostic->raise(ErrorType::Runtime, "Float number cannot be moduled.", $expression->pos[0], $this->cursor);
                case Operation::BitAnd:
                    if (($right - floatval(intval($right)) == 0) && ($left - floatval(intval($left)) == 0)) {
                        return floatval(intval($right) and intval($left));
                    }
                    $this->diagnostic->raise(ErrorType::Runtime, "Unexpected operands: float number cannot be treated within integer operation.", $expression->pos[0], $this->cursor);
                case Operation::BitOr:
                    if (($right - floatval(intval($right)) == 0) && ($left - floatval(intval($left)) == 0)) {
                        return floatval(intval($right) or intval($left));
                    }
                    $this->diagnostic->raise(ErrorType::Runtime, "Unexpected operands: float number cannot be treated within integer operation.", $expression->pos[0], $this->cursor);
            }
        }
        if ($expression instanceof Compare) {
            $left = $this->evaluate_expression($expression->left);
            $right = $this->evaluate_expression($expression->right);
            switch ($expression->operator) {
                case Comparison::Gt:
                    return $left < $right;
                case Comparison::Lt:
                    return $left > $right;
                case Comparison::LtE:
                    return $left >= $right;
                case Comparison::GtE:
                    return $left <= $right;
                case Comparison::Is:
                    return $left == $right;
                case Comparison::IsNot:
                    return $left != $right;
            }
        }
        if ($expression instanceof UnaryOperation) {
            $operand = $this->evaluate_expression($expression->operand);
            switch ($expression->operator) {
                case Unary::Not:
                    return !$operand;
                case Unary::Minus:
                    return $operand * -1;
            }
        }
        if ($expression instanceof Subscript) {
            $variable = $this->variables[($expression->identifier->value)];
            if (!is_array($variable) && !is_string($variable)) {
                $this->diagnostic->raise(ErrorType::Runtime, "Tried to index into Unslicable type.", $expression->pos[0], $this->cursor);
            }
            if ($expression->slice instanceof Index) {
                return $variable[$this->evaluate_expression($expression->slice->value)];
            }

            if ($expression->slice instanceof Slice) {
                $start = $this->evaluate_expression($expression->slice->lower);
                $stop = $this->evaluate_expression($expression->slice->upper);
                $step = $this->evaluate_expression($expression->slice->steps);
                if ($stop < 0) {
                    $stop = count($variable) - (-1 * $stop);
                }
                if ($step <= 0) {
                    throw new InvalidArgumentException("Step cannot be zero.");
                } 
                $res = array_slice($variable,$start, $stop - $start + 1);
            
                if ($step != 1) {
                    $result = [];
                    for ($i = $start; ($step > 0 ? $i < $stop : $i > $stop) ; $i += $step) {
                        if (isset($res[$i])) {
                            $result[] = $res[$i];
                        }
                    }     
                    return $result;           
                }
                return $res;
            }
        }
    }

    public function call_function($function, $args, $can_return=TRUE, &$self = null) {

        $function_object = $function;
        if (count($args) !== count($function_object->args)) {
            $this->diagnostic->raise(ErrorType::InvalidArgumentCount, "Incorrect number of arguments for function.", $function_object->pos[0], $this->cursor);
        }
        $val = null;
        
        $save_point_variables = $this->variables;
        
        foreach ($function_object->args as $index => $arg) {
            $this->variables[$arg->value] = ($arg->value == 'self' ? $args[$index] : $this->evaluate_expression($args[$index])) ;
        }


        foreach ($function_object->body as $statement) {
            if (!$statement instanceof ReturnStatement) {
                    $this->execute_statement($statement);
                } else {
                if (!$can_return) {
                    $this->diagnostic->raise(ErrorType::Runtime, "function cannot have a return value.", $statement->pos[0], $this->cursor);
                }
                $val = $this->evaluate_expression($statement->value) ?? null;
                break;
            }
        }
        if (!is_null($self)) {
            $self = $this->variables[$function_object->args[0]->value];
        }
        foreach ($function_object->args as $index => $arg) {
            unset($this->variables[$arg->value]);
        }
        return $val;
    }

    public function call_object($object) {
        $name = $object->name;
        $args = $object->args;


        $object_def = clone $this->variables[$name];
        if (!isset($object_def)) {
            $this->diagnostic->raise(ErrorType::Runtime, "Object \"$name\" is not defined.", $object->pos[0], $this->cursor);
        }
    
        if (isset($args) && isset($object_def->properties['__init'])) {
            $init_method = $object_def->properties['__init'];
            $props = null;
            if (count($init_method->args) > 0 && $init_method->args[0]->value == 'self') {
                $args = array_merge([&$object_def->properties], $args);
                $props = &$object_def->properties;
            }
            
            $this->call_function($init_method, $args, FALSE, $props);
        }
        return $object_def;
    }
    
}

