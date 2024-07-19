<?php

class Interpreter {
    private $program;
    private $source_code;
    public $variables = [];
    public $unoptimised_program;
    public $filepath;
    public $current_dir;
    private $internal_buffer = [];

    public function __construct($source_code, $filepath) {
        $this->source_code = $source_code;
        $this->filepath =  realpath($filepath);
        $this->current_dir = dirname($this->filepath);
        $lexer = new Lexer($this->source_code);
        $parser = new Parser($lexer);
        $program = $parser->parse();
        $optimizer = new Optimizer($program);
        $this->unoptimised_program = clone $optimizer->program;
        $this->program = $optimizer->optimize();
    }

    public function interpret() {
        foreach ($this->program->body as $statement) {
            $this->execute_statement($statement);
        }
        return implode('',$this->internal_buffer);
    }

    public function execute_statement($statement) {
        if ($statement instanceof Kill) {
            die;
        } elseif ($statement instanceof DeclareVariable) {
            if (array_key_exists($statement->name, $this->variables)) {
                echo "Variable \"" . $statement->name . "\" already exists.";
                die;
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
                    echo "Invalid statement in object declaration, got: " . get_class($statement_child);
                    die;
                }
            }
            $properties = array_diff_key($this->variables, $old_vars);
            foreach ($properties as $key => $value) {
                unset($this->variables[$key]);
            }

            $this->variables[$statement->name->value] = new ObjectType($name, $properties);
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
                    echo "Assigning into undeclared variable.";
                    die;
                }
                $this->variables[$name] = $this->evaluate_expression($statement->value);
                
            } elseif ($statement->left instanceof MemberAccess) {
                $object = $this->evaluate_expression($statement->left->object);
                $name = $this->evaluate_expression($statement->left->property);
                $this->variables[$statement->left->object->value][$statement->left->property->value] = $this->evaluate_expression($statement->value);
            } elseif ($statement->left instanceof Subscript && $statement->left->slice instanceof Index) {
                $left = $this->evaluate_expression($statement->identifier);
                $index = $this->evaluate_expression($statement->slice->value);
                $this->variables[$left][$index] = $this->evaluate_expression($statement->value);
            } else {
                echo "Invalid assignment.";
                die;
            }
        }
        elseif ($statement instanceof AugAssign) {
            $name = $statement->name;
            if (!isset($this->variables[$name])) {
                echo "Assigning into undeclared variable.";
                die;
            }
            $this->variables[$name] = $this->evaluate_expression(new BinaryOperation(new Identifier($statement->name), $statement->operator, $statement->value));
        } elseif ($statement instanceof BreakLoop) {
            echo "Use of break outside of a loop is illegal.";
            die;
        } elseif ($statement instanceof ContinueLoop) {
            echo "Use of continue outside of a loop is illegal.";
            die;
        } elseif ($statement instanceof IncludeStatement) {
            $source = file_get_contents($this->current_dir . '/' . $this->evaluate_expression($statement->filepath)) or die('Failed to open file: ' . $this->current_dir . '/' . $this->evaluate_expression($statement->filepath));
            $inside_interpreter = new Interpreter($source);
            $this->internal_buffer[] = $inside_interpreter->interpret($this->current_dir . '/' . $this->evaluate_expression($statement->filepath));
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
                
                echo "Unexpected Identifier \"" . $name . "\" was given to del";
                die;
            }
            unset($this->variables[$name]);
        } elseif ($statement instanceof DeclareFunction) {
            $name = $statement->name;
            $args = $statement->args;
            $body = $statement->body;

            $this->variables[$name->value] = new FunctionObject($name, $args, $body, [$this->variables, []]);

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
                echo "File not found: " . $this->current_dir . '/' . $file;
                die;
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
            $inside_interpreter->interpret($this->current_dir . '/' . $file);

            // foreach (array_reverse($inside_interpreter->program->body) as $key => $value) {
            //     if ($value instanceof DeleteVariable) {

            //     }
            // }
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
            if (in_array($expression->name, get_class_methods('StdLib'))) {
                $args = [];
                foreach ($expression->args as $arg) {
                    array_push($args, $this->evaluate_expression($arg));
                }
                
                return call_user_func_array([new StdLib(), $expression->name], $args);
            } 
            if ($this->variables[$expression->name] instanceof ObjectType) {
                return $this->call_object($expression);
            }
            if (!array_key_exists($expression->name, $this->variables)) {
                echo "Found call for undeclared function \"" . $expression->name . "\".";
                die;
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
                    echo "Found call for undeclared property \"" . $expression->property->value . "\".";
                    die;
                }
                return $object->properties[$expression->property->value];
            } else {
                return $object[$expression->property->value];

            }
        }

        if ($expression instanceof Identifier) {
            if (!array_key_exists($expression->value, $this->variables)) {
                echo "Found undeclared identifier \"" . $expression->value . "\".";
                die;
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
                    echo "Unexpected operands: float number cannot be moduled.";
                    die;
                case Operation::BitAnd:
                    if (($right - floatval(intval($right)) == 0) && ($left - floatval(intval($left)) == 0)) {
                        return floatval(intval($right) and intval($left));
                    }
                    echo "Unexpected operands: float number cannot be treated within int operation.";
                    die;
                case Operation::BitOr:
                    if (($right - floatval(intval($right)) == 0) && ($left - floatval(intval($left)) == 0)) {
                        return floatval(intval($right) or intval($left));
                    }
                    echo "Unexpected operands: float number cannot be treated within int operation.";
                    die;
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
            $operand = $this->evaluate_expression($expression->left);
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
                echo "Presented unslicable type.";
                die;
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
            echo "Incorrect number of arguments for function.";
            die;
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
                    echo "Function cannot have a return value.";
                    die;
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
            echo "Object \"$name\" is not defined.";
            die;
        }
    
        if (isset($args) && isset($object_def->properties['init'])) {
            $init_method = $object_def->properties['init'];
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

?>