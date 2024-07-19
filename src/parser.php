<?php
class Parser {
    public $tokens;
    public $pos = 0;
    private $echo_tracker = 0;
    private $precedence = [
        '&' => 0,
        '|' => 0,
        '+' => 1,
        '-' => 1,
        '%' => 2,
        '*' => 2,
        '/' => 2,
        '==' => 3,
        '!=' => 3,
        '<' => 3,
        '>' => 3,
        '<=' => 3,
        '>=' => 3,
        '&&' => 4,
        '||' => 4
    ];
    private $comparison = [
        '>' => Comparison::Gt,
        '<' => Comparison::Lt,
        '<=' => Comparison::LtE,
        '>=' => Comparison::GtE,
        '!=' => Comparison::IsNot,
        '==' => Comparison::Is,
    ];
    private $operation = [
        '+' => Operation::Add,
        '-' => Operation::Sub,
        '/' => Operation::Div,
        '*' => Operation::Mult,
        '%' => Operation::Modulo,
    ];

    private $bool = [
        '&&' => BoolOperand::And,
        '||' => BoolOperand::Or
    ];

    private $unary = [
        '!' => Unary::Not,
        '-' => Unary::Minus,
        '~' => Unary::Negate
    ];

    private $operators = [
    '+' => Operation::Add,
    '-' => Operation::Sub,
    '/' => Operation::Div,
    '*' => Operation::Mult,
    '%' => Operation::Modulo,
    '&' => Operation::BitAnd,
    '|' => Operation::BitOr

    ];

    public function __construct(Lexer $lexer_object) {
        $this->tokens = $lexer_object->lex();
    }

    private function currentToken() {
        return $this->tokens[$this->pos];
    }

    private function nextToken() {
        $this->pos++;
    }

    private function expect($type, $value = null) {
        $token = $this->currentToken();
        if ($token->type !== $type || ($value !== null && $token->value !== $value)) {
            echo ("Unexpected token: " . $token->type . ", Expected: " . $type . " " . $value);
            die;
        }
        $this->nextToken();
    }

    public function parse() {
        $program = new Program();

        while ($this->currentToken()->type !== 'EOF') {
            $statement = $this->parse_statement();
            if ($statement !== null) {
                array_push($program->body, $statement);
                if ($this->currentToken()->type == 'SEMI_COLON') {
                    $this->nextToken();
                }
            }
        }

        return $program;
    }

    private function parse_statement() {
        $token = $this->currentToken();

        if ($token->type == 'TEXT') {
            $this->nextToken();
            return new LiteralText($token->value);
        }
        #if ($token->type === 'IDENTIFIER' && $this->tokens[$this->pos + 1]->type === 'EQUAL') {
            
        // } elseif ($token->type === 'IDENTIFIER' && $this->tokens[$this->pos + 1]->type === 'ASSIGN_OPERATOR') {
        //     return $this->parse_argumented_assign();
        // }

        if ($token->type === 'KEYWORD') {
            switch ($token->value) {
                case 'echo':
                    return $this->parse_echo();
                case 'let':
                    return $this->parse_variable_declaration();
                case 'if':
                    return $this->parse_if_statement();
                case 'loop':
                    return $this->parse_loop_statement();
                case 'return':
                    return $this->parse_return_statement();
                case 'def':
                    return $this->parse_function_defination();
                case 'set':
                    return $this->parse_set_statement();
                case 'del':
                    return $this->parse_del();
                case 'def':
                    return $this->parse_function_defination();
                case 'inclass':
                    $this->nextToken();
                    return $this->parse_function_defination(TRUE);
                case 'class':
                    return $this->parse_object();
                case 'die':
                    return $this->parse_die();
                case 'include':
                    return $this->parse_include();
            }
        }
    
        $expr = $this->parse_expression();
        $this->expect('SEMI_COLON');
        return $expr;
    }

    private function parse_set_statement() {
        if ($this->currentToken()->type == 'KEYWORD') {
            $this->nextToken();
        }
        $left = $this->parse_primary_expression();

        $this->expect('EQUAL');
        $value = $this->parse_expression();
        $token = $this->currentToken();

        $var_reassign = new AssignVariable($name, $value);
        $this->expect('SEMI_COLON'); 

        return $var_reassign;
    }

    private function parse_argumented_assign() {
        if ($this->currentToken()->type == 'KEYWORD' && $this->currentToken()->value == 'set') {
            $this->nextToken();
        } elseif ($this->currentToken()->type == 'KEYWORD' && $this->currentToken()->value != 'set') {
            echo "Unexpected token: " . $this->currentToken()->type;
            die;
        }
        $name = $this->currentToken()->value;
        $this->expect('IDENTIFIER');

        $operator = $this->currentToken();
        $this->expect('ASSIGN_OPERATOR');

        $value = $this->parse_expression();
        $var_reassign = new AugAssign($name,$this->operators[$operator->value[0]],  $value);
        $this->expect('SEMI_COLON'); 

        return $var_reassign;
    }

    private function parse_die() {
        $this->expect('KEYWORD', 'die');
        $this->expect('SEMI_COLON');
        return new Kill();
    }
    private function parse_function_defination($is_inclass=FALSE) {
        $this->expect('KEYWORD', 'def');
        $token = $this->currentToken();
        if ($token->type !== "IDENTIFIER") {
            echo ("Unexpected token: " . $token->type);
            die;
        }
        $name = new Identifier($token->value);
        $this->nextToken();
        $this->expect('OPEN_PAREN');
        $args = [];
        while ($this->currentToken()->type != 'CLOSE_PAREN' && $this->currentToken()->type != "EOF") {

            if ($this->currentToken()->type !== "IDENTIFIER") {
                echo ("Unexpected token: " . $this->currentToken()->type);
                die;
            }
            array_push($args, new Identifier($this->currentToken()->value));
            $this->nextToken();
            if ($this->currentToken()->type !== "COMMA" && $this->currentToken()->type !== "CLOSE_PAREN" ) {
                echo ("Unexpected token: " . $this->currentToken()->type);
                die;
            }
            if ($this->currentToken()->type === "COMMA") {
                $this->nextToken();
            }
        }
        $this->nextToken();
        $this->expect("OPEN_CURLY");
        $body = [];
        while ($this->currentToken()->type !== 'CLOSE_CURLY') {
            $body[] = $this->parse_statement();
        }
        $this->expect('CLOSE_CURLY');
        return new DeclareFunction($name, $args, $body, $is_inclass);
    }

    private function parse_return_statement() {
        $this->expect('KEYWORD', 'return');
        $value = $this->parse_expression();
        $this->expect('SEMI_COLON');
        
        return new ReturnStatement($value);
    }

    private function parse_include() {
        $this->expect('KEYWORD', 'include');
        $value = $this->parse_expression();
        $this->expect('SEMI_COLON');

        return new IncludeStatement($value);
    }


    private function parse_loop_statement() {
        $this->expect('KEYWORD', 'loop');
        $condition = $this->parse_expression();
        $this->expect('OPEN_CURLY');
        
        $body = [];
        while ($this->currentToken()->type !== 'CLOSE_CURLY') {
            $body[] = $this->parse_statement();
        }
        $this->expect('CLOSE_CURLY');
        
        return new LoopStatement($condition, $body, $this->currentToken()->position);
    }

    private function parse_object() {
        $this->expect('KEYWORD', 'class');
        $name = $this->parse_expression();
        if (!$name instanceof Identifier) {
            echo 'Expected Identifier after object, not ' . $name . '.';
            die;
        }
        $this->expect('OPEN_CURLY');

        $body = [];
        while ($this->currentToken()->type !== 'CLOSE_CURLY') {
            $body[] = $this->parse_statement();
        }
        $this->expect('CLOSE_CURLY');
        
        return new ObjectDeclare($name, $body);
    }
    

    private function parse_del() {
        $this->expect('KEYWORD', 'del');
        if ($this->currentToken()->type != 'IDENTIFIER') {
            echo 'Expected Identifier after del, not ' . $this->currentToken()->type . '.';
            die; 
        }
        $name = $this->currentToken();
        $this->nextToken();
        $this->expect('SEMI_COLON');
        return new DeleteVariable($name);
    }
    
    private function parse_if_statement($original_if = TRUE) {
        $this->expect('KEYWORD', 'if');
        $condition = $this->parse_expression();
        $this->expect('OPEN_CURLY');
        
        $body = [];
        while ($this->currentToken()->type !== 'CLOSE_CURLY') {
            $body[] = $this->parse_statement();
        }
        $this->expect('CLOSE_CURLY');
        $tryother = [];
        $else = null;
        
        if ($original_if) {
            if ($this->currentToken()->type === 'KEYWORD' && $this->currentToken()->value === 'elif') {
                $tryother[] = $this->parse_if_statement(FALSE);
            } elseif ($this->currentToken()->type === 'KEYWORD' && $this->currentToken()->value === 'else') {
                $this->expect('KEYWORD', 'else');
                $this->expect('OPEN_CURLY');
                
                $else = [];
                while ($this->currentToken()->type !== 'CLOSE_CURLY') {
                    $else[] = $this->parse_statement();
                }
                $this->expect('CLOSE_CURLY');
            }
        }
        
        return new IfStatement($condition, $body, $tryother, $else, $this->currentToken()->position);
    }


    

    private function parse_echo() {
        $this->expect('KEYWORD', 'echo');

        $expression = $this->parse_expression();
        $token = $this->currentToken();

        $echo_statement = new EchoStatement($expression, $token->position, $this->echo_tracker++);
        $this->expect('SEMI_COLON'); 

        return $echo_statement;
    }

    private function parse_variable_declaration() {
        $this->expect('KEYWORD', 'let');

        $name = $this->currentToken()->value;
        $this->expect('IDENTIFIER');

        $this->expect('EQUAL');

        $value = $this->parse_expression();
        $token = $this->currentToken();

        $var_declaration = new DeclareVariable($name, $value, $token->position);
        
        $this->expect('SEMI_COLON'); 

        return $var_declaration;
    }
    private function parse_expression($precedence = 0) {
        $left = $this->parse_primary_expression();
        while ($this->currentToken()->type === 'OPERATOR' && $this->precedence[$this->currentToken()->value] > $precedence) {
            $operator = $this->currentToken()->value;
            $this->nextToken();
            $right = $this->parse_expression($this->precedence[$operator]);

            if (in_array($operator, ['<', '>', '<=', '>=', '!=', '=='])) {
                $left = new Compare($left, $this->comparison[$operator] , $right);
            } elseif (in_array($operator, ['&&', '||'])) {
                $left = new BoolOp($left, $this->bool[$operator] , $right);
            } else {
                $left = new BinaryOperation($left, $this->operation[$operator], $right);
            }
        }
        return $left;
    }

    private function parse_primary_expression() {

        $token = $this->currentToken();

        if ($token->value === '-') {
            $this->nextToken();
            $operand = $this->parse_primary_expression();
            return new UnaryOperation(Unary::Minus, $operand); 
        } elseif ($token->type === 'NOT') {
            $this->nextToken();
            $operand = $this->parse_primary_expression();
            return new UnaryOperation(Unary::Not, $operand); 
        } elseif ($token->type === 'NEGATE') {
            $this->nextToken();
            $operand = $this->parse_primary_expression();
            return new UnaryOperation(Unary::Negate, $operand); 
        }
        if ($token->type === 'STRING') {
            $value = $token->value;
            $this->nextToken();
            return new StringLiteral($value);
        } elseif ($token->type === 'NUMBER') {
            $value = $token->value;
            $this->nextToken();
            return new NumberLiteral($value);
        } elseif ($token->value == "none") {
            $this->nextToken();
            return new None();
        } elseif ($token->type === 'IDENTIFIER') {
            $ident = $this->parse_identifier();

            if ($this->currentToken()->type == 'EQUAL' && !$ident instanceof FunctionCall) { 
                $this->expect('EQUAL');
                $value = $this->parse_expression();
                $var_reassign = new AssignVariable($ident, $value);
                return $var_reassign;
            }
            
            elseif ($this->currentToken()->type == 'ASSIGN_OPERATOR') {
                $operator = $this->currentToken()->value;
                $this->nextToken();
                $value = $this->parse_expression();
                return new AugAssign($ident->value,$this->operators[$operator[0]], $value);
            } 

            elseif ($this->currentToken()->type == 'OPEN_PAREN') {

                $args = [];
                $this->nextToken();
                while ($this->currentToken()->type != 'CLOSE_PAREN') {
                    $args[] = $this->parse_expression();
                    if ($this->currentToken()->type != 'CLOSE_PAREN') {
                        $this->expect('COMMA');
                    }
                }
                $this->expect('CLOSE_PAREN');
                return new FunctionCall($ident, $args);
                }
            return $ident;


        }
        elseif ($token->type === 'OPEN_PAREN') {
            $this->nextToken();
            $expression = $this->parse_expression();
            $this->expect('CLOSE_PAREN');
            return $expression;
            
        } elseif ($token->value === 'import') {
            $this->expect('KEYWORD', 'import');
            $filepath = $this->parse_expression();
            return new ImportStatement($filepath);
        }
        echo "Unexpected token type: " . $token->type ;
        die;

    }

    private function parse_identifier() {
        $token = $this->currentToken();
        $value = $token->value;
            $ident = new Identifier($value);
            $this->nextToken();
            if ($this->currentToken()->type == 'PERIOD') {
                $this->nextToken();
                $object = new MemberAccess(new Identifier($value), $this->parse_identifier());
                $ident = $object;
            }
            elseif ($this->currentToken()->type == 'OPEN_PAREN') {
                $args = [];
                $this->nextToken();
                while ($this->currentToken()->type != 'CLOSE_PAREN' && $this->currentToken()->type != "EOF") {
                    array_push($args, $this->parse_expression());
                    
                    if ($this->currentToken()->type !== "COMMA" && $this->currentToken()->type !== "CLOSE_PAREN" ) {
                        echo ("Unexpected token: " . $this->currentToken()->type);
                        die;
                    }
                    if ($this->currentToken()->type === "COMMA") {
                        $this->nextToken();
                    }
                }
                $this->nextToken();
                $ident = new FunctionCall($value, $args);
            }
            elseif ($this->currentToken()->type == 'OPEN_BRACKET') {
                $lower = null;
                $upper = null;
                $steps = null;
                $this->nextToken();
                if ($this->currentToken()->type != "COLON" && $this->tokens[$this->pos+1]->type == 'CLOSE_BRACKET') {
                    $expression = $this->parse_expression();
                    $this->nextToken();
                    return new Subscript(new Identifier($value), new Index($expression));
                }
                $notation = "";
                $start_not = [];
                $i = 0;
                while ($this->currentToken()->type != 'CLOSE_BRACKET' && $this->currentToken()->type != "EOF") {
                    if (!isset($start_not[$i][0])) {
                        array_push($start_not, [$this->pos]);
                    }
                    $notation .= "{$this->currentToken()->value}";
                    $this->nextToken();
                    if ($this->currentToken()->type == 'COLON') {
                        array_push($start_not[$i], $this->pos);
                        $i += 1;
                    }
                }
                array_push($start_not[$i], $this->pos);
                $components = explode(':', $notation);
                $start = isset($components[0]) ? TRUE : null;
                $stop = isset($components[1]) ? TRUE : null;
                $step = isset($components[2]) ? TRUE : null;

                if ($start == TRUE) {
                    $internal_parser = new Parser(new Lexer(""));
                    $internal_parser->tokens = array_merge(
                        array_slice($this->tokens , $start_not[0][0], $start_not[0][1] - $start_not[0][0]), 
                        [new Token("EOF", '\0', [$start_not[0][1] - $start_not[0][0], $start_not[0][1] - $start_not[0][0]])]
                    );
                    $internal_program = $internal_parser->parse();
                    $start = $internal_program->body[0]; 
                } else {
                    $start = new NumberLiteral('0');
                }

                if ($stop == TRUE) {
                    $internal_parser = new Parser(new Lexer(""));
                    $internal_parser->tokens = array_merge(
                        array_slice($this->tokens , $start_not[1][0] + 1, $start_not[1][1] - ($start_not[1][0] + 1)), 
                        [new Token("EOF", '\0', [$start_not[1][1] - $start_not[1][0], $start_not[1][1] - $start_not[1][0] ])]
                    );
                    if (count($internal_parser->tokens) == 1) {
                        $stop = new UnaryOperation(Unary::Minus, new NumberLiteral('1'));
                    }
                    else {
                        $internal_program = $internal_parser->parse();
                        $stop = $internal_program->body[0];
                    }
                } else {
                    $stop = new UnaryOperation(Unary::Minus, new NumberLiteral('1'));
                }

                if ($step == TRUE) {
                    $internal_parser = new Parser(new Lexer(""));
                    $internal_parser->tokens = array_merge(
                        array_slice($this->tokens , $start_not[2][0] + 1, $start_not[2][1] - ($start_not[2][0] + 1)), 
                        [new Token("EOF", '\0', [$start_not[2][0] + 1, $start_not[2][0] + 1])]
                    );
                    $internal_program = $internal_parser->parse();
                    $step = $internal_program->body[0];
                } else {
                    $step = new NumberLiteral('1');
                }
                $this->nextToken();
                $ident = new Subscript(new Identifier($value), new Slice($start, $stop, $step));

            }
            
            return $ident;
    }
}
?>