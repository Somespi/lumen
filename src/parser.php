<?php
class Parser {
    public $tokens;
    public $pos = 0;
    public $diagnostic;
    public $cursor;
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
        $this->cursor = new Cursor($lexer_object->source, $lexer_object->filepath);
        $this->diagnostic = new Diagnostic();
    }

    private function currentToken() {
        $this->cursor->goto($this->tokens[$this->pos]->position[1]);
        return $this->tokens[$this->pos];
    }

    private function nextToken() {
        $this->pos++;
        $token = $this->tokens[$this->pos];
        $this->cursor->goto($token->position[1]);
    }

    private function expect($type, $value = null) {
        $token = $this->currentToken();
        if ($token->type !== $type || ($value !== null && $token->value !== $value)) {
            $this->cursor->goto($token->position[1]);
            $this->diagnostic->raise(ErrorType::Syntax, "Unexpected token: \"" . $token->value. "\" , Expected type of: " . strtolower($type) . " " . $value, $token->position[0], $this->cursor);
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
            return new LiteralText($token->value, $token->position);
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
                case 'del':
                    return $this->parse_del();
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
        $token = $this->currentToken();
        $left = $this->parse_primary_expression();

        $this->expect('EQUAL');
        $value = $this->parse_expression();

        $var_reassign = new AssignVariable($token->name, $value, [$token->position[0], $this->currentToken()->position[1]]);
        $this->expect('SEMI_COLON'); 

        return $var_reassign;
    }


    private function parse_die() {
        $this->expect('KEYWORD', 'die');
        $this->expect('SEMI_COLON');
        return new Kill($this->currentToken()->position);
    }
    private function parse_function_defination() {
        $this->expect('KEYWORD', 'def');
        $token = $this->currentToken();
        if ($token->type !== "IDENTIFIER") {
            $this->diagnostic->raise(ErrorType::Syntax, "Expected identifier for function name, found: " . $token->value . "", $token->position[0], $this->cursor);
        }
        $name = new Identifier($token->value, $token->position);
        $this->nextToken();
        $this->expect('OPEN_PAREN');
        $args = [];
        while ($this->currentToken()->type != 'CLOSE_PAREN' && $this->currentToken()->type != "EOF") {

            if ($this->currentToken()->type !== "IDENTIFIER") {
                $this->diagnostic->raise(ErrorType::Syntax, "Expected identifier for function declaration, found: " . $this->currentToken()->value . "", $this->currentToken()->position[0], $this->cursor);
            
            }
            array_push($args, new Identifier($this->currentToken()->value, $this->currentToken()->position));
            $this->nextToken();
            if ($this->currentToken()->type !== "COMMA" && $this->currentToken()->type !== "CLOSE_PAREN" ) {
                $this->diagnostic->raise(ErrorType::Syntax, "Expected comma or close paren for function declaration, found: " . $this->currentToken()->value . "", $this->currentToken()->position[0], $this->cursor);
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
        $end = $this->currentToken();
        $this->expect('CLOSE_CURLY');
        return new DeclareFunction($name, $args, $body, [$token->position[0], $end->position[1]]);
    }

    private function parse_return_statement() {
        $start = $this->currentToken()->position[0];
        $this->expect('KEYWORD', 'return');
        $value = $this->parse_expression();
        $end = $this->currentToken()->position[1];
        $this->expect('SEMI_COLON');
        
        return new ReturnStatement($value, [$start, $end]);
    }

    private function parse_include() {
        $start = $this->currentToken()->position[0];
        $this->expect('KEYWORD', 'include');
        $value = $this->parse_expression();
        $end = $this->currentToken()->position[1];
        $this->expect('SEMI_COLON');

        return new IncludeStatement($value, [$start, $end]);
    }


    private function parse_loop_statement() {
        $start = $this->currentToken()->position[0];
        $this->expect('KEYWORD', 'loop');
        $condition = $this->parse_expression();
        $this->expect('OPEN_CURLY');
        
        $body = [];
        while ($this->currentToken()->type !== 'CLOSE_CURLY') {
            $body[] = $this->parse_statement();
        }
        $end = $this->currentToken()->position[1];
        $this->expect('CLOSE_CURLY');
        
        return new LoopStatement($condition, $body, [$start, $end]);
    }

    private function parse_object() {
        $start = $this->currentToken()->position[0];
        $this->expect('KEYWORD', 'class');
        $name = $this->parse_expression();
        if (!$name instanceof Identifier) {
            $this->diagnostic->raise(ErrorType::Syntax, 'Expected Identifier after object, not ' . $name . '.', $name->position[0], $this->cursor);
        }
        $this->expect('OPEN_CURLY');

        $body = [];
        while ($this->currentToken()->type !== 'CLOSE_CURLY') {
            $body[] = $this->parse_statement();
        }
        $end = $this->currentToken()->position[1];
        $this->expect('CLOSE_CURLY');
        
        return new ObjectDeclare($name, $body, [$start, $end]);
    }
    

    private function parse_del() {
        $start = $this->currentToken()->position[0];
        $this->expect('KEYWORD', 'del');
        if ($this->currentToken()->type != 'IDENTIFIER') {
            $this->diagnostic->raise(ErrorType::Syntax, 'Expected Identifier after del, not ' . $this->currentToken()->type . '.', $this->currentToken()->position[0], $this->cursor);
        }
        $name = $this->currentToken();
        $this->nextToken();
        $end = $this->currentToken()->position[1];
        $this->expect('SEMI_COLON');
        return new DeleteVariable($name, [$start, $end]);
    }
    
    private function parse_if_statement($original_if = TRUE) {
        $start = $this->currentToken()->position[0];
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
        $end = $this->currentToken()->position[1];
        return new IfStatement($condition, $body, $tryother, $else, [$start, $end]);
    }


    

    private function parse_echo() {
        $start = $this->currentToken()->position[0];
        $this->expect('KEYWORD', 'echo');

        $expression = $this->parse_expression();
        $token = $this->currentToken();
        $end = $this->currentToken()->position[1];
        $echo_statement = new EchoStatement($expression, [$start, $end]);
        $this->expect('SEMI_COLON'); 

        return $echo_statement;
    }

    private function parse_variable_declaration() {
        $start = $this->currentToken()->position[0];
        $this->expect('KEYWORD', 'let');

        $name = $this->currentToken()->value;
        $this->expect('IDENTIFIER');

        $this->expect('EQUAL');

        $value = $this->parse_expression();
        $token = $this->currentToken();

        $var_declaration = new DeclareVariable($name, $value, [$start, $token->position[1]]);
        
        $this->expect('SEMI_COLON'); 

        return $var_declaration;
    }
    private function parse_expression($precedence = 0) {
        $start = $this->currentToken()->position[0];
        $left = $this->parse_primary_expression();
        while ($this->currentToken()->type === 'OPERATOR' && $this->precedence[$this->currentToken()->value] > $precedence) {
            $operator = $this->currentToken()->value;
            $this->nextToken();
            $right = $this->parse_expression($this->precedence[$operator]);
            $pos = [$start, $this->currentToken()->position[1]];
            if (in_array($operator, ['<', '>', '<=', '>=', '!=', '=='])) {
                $left = new Compare($left, $this->comparison[$operator] , $right, $pos);
            } elseif (in_array($operator, ['&&', '||'])) {
                $left = new BoolOp($left, $this->bool[$operator] , $right, $pos);
            } else {
                $left = new BinaryOperation($left, $this->operation[$operator], $right, $pos);
            }
        }
        return $left;
    }

    private function parse_primary_expression() {
        $start = $this->currentToken()->position[0];
        $token = $this->currentToken();

        if ($token->value === '-') {
            $this->nextToken();
            $operand = $this->parse_primary_expression();
            return new UnaryOperation(Unary::Minus, $operand, [$start, $this->currentToken()->position[1]]); 
        } elseif ($token->type === 'NOT') {
            $this->nextToken();
            $operand = $this->parse_primary_expression();
            return new UnaryOperation(Unary::Not, $operand, [$start, $this->currentToken()->position[1]]); 
        } elseif ($token->type === 'NEGATE') {
            $this->nextToken();
            $operand = $this->parse_primary_expression();
            return new UnaryOperation(Unary::Negate, $operand, [$start, $this->currentToken()->position[1]]); 
        }
        if ($token->type === 'STRING') {
            $value = $token->value;
            $this->nextToken();
            return new StringLiteral($value, $token->position);
        } elseif ($token->type === 'NUMBER') {
            $value = $token->value;
            $this->nextToken();
            return new NumberLiteral($value, $token->position);
        } elseif ($token->value == "none") {
            $this->nextToken();
            return new None($token->position);
        } elseif ($token->type === 'IDENTIFIER') {
            $start = $this->currentToken()->position[0];
            $ident = $this->parse_identifier();

            if ($this->currentToken()->type == 'EQUAL' && !$ident instanceof FunctionCall) { 
                $this->expect('EQUAL');
                $value = $this->parse_expression();
                $var_reassign = new AssignVariable($ident, $value, [$start, $this->currentToken()->position[1]]);
                return $var_reassign;
            }
            
            elseif ($this->currentToken()->type == 'ASSIGN_OPERATOR') {
                $operator = $this->currentToken()->value;
                $this->nextToken();
                $value = $this->parse_expression();
                return new AugAssign($ident->value,$this->operators[$operator[0]], $value, [$start, $this->currentToken()->position[1]]);
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
                return new FunctionCall($ident, $args, [$start, $this->currentToken()->position[1]]);
                }
            return $ident;


        }
        elseif ($token->type === 'OPEN_PAREN') {
            $this->nextToken();
            $expression = $this->parse_expression();
            $this->expect('CLOSE_PAREN');
            return $expression;
            
        } elseif ($token->value === 'import') {
            $start = $this->currentToken()->position[0];
            $this->expect('KEYWORD', 'import');
            $filepath = $this->parse_expression();
            return new ImportStatement($filepath, [$start, $this->currentToken()->position[1]]);
        }

        
        $this->diagnostic->raise(ErrorType::Syntax, "Unexpected token: \"{$token->value}\" , Expected expression.", $token->position[0], $this->cursor);


    }

    private function parse_identifier() {
        $token = $this->currentToken();
        $value = $token->value;
            $ident = new Identifier($value, $token->position);
            $this->nextToken();
            if ($this->currentToken()->type == 'PERIOD') {
                $this->nextToken();
                $object = new MemberAccess(new Identifier($value, $token->position), $this->parse_identifier(), [$ident->pos[1], $this->currentToken()->position[1]]);
                $ident = $object;
            }
            elseif ($this->currentToken()->type == 'OPEN_PAREN') {
    
                $args = [];
                $this->nextToken();
                while ($this->currentToken()->type != 'CLOSE_PAREN' && $this->currentToken()->type != "EOF") {
                    array_push($args, $this->parse_expression());
                    
                    if ($this->currentToken()->type !== "COMMA" && $this->currentToken()->type !== "CLOSE_PAREN" ) {
                        $this->diagnostic->raise(ErrorType::Syntax, "Unexpected token: \"" . $this->currentToken()->value. "\" , Expected comma or close parenthesis for function call.", $this->currentToken()->position[0], $this->cursor);
                    }
                    if ($this->currentToken()->type === "COMMA") {
                        $this->nextToken();
                    }
                }
                $this->nextToken();
                $ident = new FunctionCall($value, $args, [$token->position[0], $this->currentToken()->position[1]]);
            }
            elseif ($this->currentToken()->type == 'OPEN_BRACKET') {

                $lower = null;
                $upper = null;
                $steps = null;
                $this->nextToken();
                if ($this->currentToken()->type != "COLON" && $this->tokens[$this->pos+1]->type == 'CLOSE_BRACKET') {
                    $index_tok = $this->currentToken();
                    $expression = $this->parse_expression();
                    $this->nextToken();
                    return new Subscript(new Identifier($value, $token->position), new Index($expression, $index_tok->position), [$ident->pos[0], $this->currentToken()->position[1]]);
                }
                $sl_rt = $this->currentToken()->position[0];
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

            switch ($start) {
                case TRUE:
                    $internal_parser = new Parser(new Lexer(""));
                    $internal_parser->tokens = array_merge(
                        array_slice($this->tokens, $start_not[0][0], $start_not[0][1] - $start_not[0][0]),
                        [new Token("SEMI_COLON", ';', [$start_not[0][1] - $start_not[0][0], $start_not[0][1] - $start_not[0][0]])],
                        [new Token("EOF", '\0', [$start_not[0][1] - $start_not[0][0], $start_not[0][1] - $start_not[0][0]])]
                    );
                    
                    $internal_program = $internal_parser->parse();
                    $start = $internal_program->body[0];
                    break;
                default:
                    $start = new NumberLiteral('0', $token->position);
                    break;
            }

            switch ($stop) {
                case TRUE:
                    $internal_parser = new Parser(new Lexer(""));
                    $internal_parser->tokens = array_merge(
                        array_slice($this->tokens, $start_not[1][0] + 1, $start_not[1][1] - ($start_not[1][0] + 1)),
                        [new Token("SEMI_COLON", ';', [$start_not[0][1] - $start_not[0][0], $start_not[0][1] - $start_not[0][0]])],
                        [new Token("EOF", '\0', [$start_not[1][1] - $start_not[1][0], $start_not[1][1] - $start_not[1][0]])]
                    );
                    switch (count($internal_parser->tokens)) {
                        case 1:
                            $stop = new UnaryOperation(Unary::Minus, new NumberLiteral('1', $token->position), $token->position);
                            break;
                        default:
                            $internal_program = $internal_parser->parse();
                            $stop = $internal_program->body[0];
                            break;
                    }
                    break;
                default:
                    $stop = new UnaryOperation(Unary::Minus, new NumberLiteral('1', $token->position), $token->position);
                    break;
            }

            switch ($step) {
                case TRUE:
                    $internal_parser = new Parser(new Lexer(""));
                    $internal_parser->tokens = array_merge(
                        array_slice($this->tokens, $start_not[2][0] + 1, $start_not[2][1] - ($start_not[2][0] + 1)),
                        [new Token("SEMI_COLON", ';', [$start_not[0][1] - $start_not[0][0], $start_not[0][1] - $start_not[0][0]])],
                        [new Token("EOF", '\0', [$start_not[2][0] + 1, $start_not[2][0] + 1])]
                    );
                    $internal_program = $internal_parser->parse();
                    $step = $internal_program->body[0];
                    break;
                default:
                    $step = new NumberLiteral('1', $token->position);
                    break;
            }
                $sl_op = $this->currentToken()->position[1];
                $this->nextToken();
                $ident = new Subscript(new Identifier($value, $token->position), new Slice($start, $stop, $step, [$sl_rt, $sl_op]), [$ident->pos[0], $this->currentToken()->position[1]]);

            }
            
            return $ident;
    }
}
