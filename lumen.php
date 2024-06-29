<?php

$keywords = ['let', 'if', 'else', 'elif', 'die', 'loop', 'def', 'return', 'echo', 'include'];

class Token {
    public $type;
    public $value;
    public $position;
    public function __construct($type = null, $value = null, $position) {
        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
    }
}

class Lexer {
    public $source;
    private $tokens = [];
    private $pos = 0;

    public function __construct($source) {
        $this->source = $source . ' ';
    }

    private function push_token($type, $value, $start=null, $end=null) {
        array_push($this->tokens, new Token($type, $value, [is_null($start) ? $this->pos : $start, is_null($end) ? $this->pos : $end]));
    }

    private function char() {
        return $this->source[$this->pos];
    }

    public function lex() {
        while ($this->pos < strlen($this->source)) {
            $char = $this->char();
            if (is_numeric($char)) {
                $this->lex_number();
            } elseif (in_array(strtolower($char), range('a', 'z'))) {
                $this->lex_identifier();
            } elseif ($char == '"' || $char == "'") {
                $this->lex_string();
            } elseif ($char == '/') {
                $this->push_token("BACK_SLASH", $this->char());
                $this->pos++;
            } elseif ($char == '=') {
                if ($this->source[$this->pos + 1] == '=') {
                    $this->pos+=2;
                    $this->push_token("OPERATOR", "==");
                } else {
                    $this->push_token("EQUAL", $this->char());
                    $this->pos++;
                }
                $this->pos++;
            } elseif ($char == '(') {
                $this->push_token("OPEN_PAREN", $this->char());
                $this->pos++;
            } elseif ($char == ')') {
                $this->push_token("CLOSE_PAREN", $this->char());
                $this->pos++;
            } elseif ($char == ',') {
                $this->push_token("COMMA", $this->char());
                $this->pos++;
            } elseif ($char == '{') {
                $this->push_token("OPEN_CURLY", $this->char());
                $this->pos++;
            } elseif ($char == '}') {
                $this->push_token("CLOSE_CURLY", $this->char());
                $this->pos++;
            } elseif ($char == ';') {
                $this->push_token("SEMI_COLON", $this->char());
                $this->pos++;
            } elseif ($char == '$') {
                $this->push_token("DOLLAR_SIGN", $this->char());
                $this->pos++;
            } elseif ($char == '?') {
                $this->push_token("QUESTION_MARK", $this->char());
                $this->pos++;
            }elseif ($char == '+') {
                $this->push_token("OPERATOR", $this->char());
                $this->pos++;
            }elseif ($char == '-') {
                $this->push_token("OPERATOR", $this->char());
                $this->pos++;
            }elseif ($char == '*') {
                $this->push_token("OPERATOR", $this->char());
                $this->pos++;
            }elseif ($char == '/') {
                $this->push_token("OPERATOR", $this->char());
                $this->pos++;
            } elseif ($char == '!') {
                if ($this->source[$this->pos + 1] == '=') {
                    $this->pos+=2;
                    $this->push_token("OPERATOR", "!=");
                } else {
                    $this->push_token("NOT", $this->char());
                    $this->pos++;
                }
            }
            elseif ($char == '<') {
                if ($this->source[$this->pos + 1] == '=') {
                    $this->pos+=2;
                    $this->push_token("OPERATOR", "<=");
                } else {
                    $this->push_token("OPERATOR", $this->char());
                    $this->pos++;
                }
            }elseif ($char == '>') {
                if ($this->source[$this->pos + 1] == '=') {
                    $this->pos+=2;
                    $this->push_token("OPERATOR", ">=");
                } else {
                    $this->push_token("OPERATOR", $this->char());
                    $this->pos++;
                }
            } elseif (empty(trim($char))) {
                $this->pos++;
            } else {
                $this->pos++;
            }
        }
        $this->push_token('EOF', '\0');
        return $this->tokens;
    }

    private function lex_number() {
        $number = $this->char();
        $start = $this->pos;
        $has_dot = false;
        $this->pos++;
        while ($this->pos < strlen($this->source) && (is_numeric($this->char()) || $this->char() == '.')) {
            if ($this->char() == '.') {
                if ($has_dot) {
                    echo ('More than one floating point in a number.');
                    die;
                }
                $has_dot = true;
            }
            $number .= $this->char();
            $this->pos++;
        }
        $this->push_token("NUMBER", $number, $start, $this->pos-1);
    }

    private function lex_identifier() {
        $identifier = $this->char();
        $start = $this->pos;
        $this->pos++;
        $legible_chars = array_merge(range('0','9'), range('A', 'Z'), range('a','z'), ['_']);
        while ($this->pos < strlen($this->source) && in_array($this->char(), $legible_chars)) {
            $identifier .= $this->char();
            $this->pos++;
        }
        $this->push_token(in_array($identifier, $GLOBALS['keywords']) ? "KEYWORD" : "IDENTIFIER", $identifier,  $start, $this->pos-1);
    }

    private function lex_string() {
        $opening_quote = $this->char();
        $start = $this->pos;
        $this->pos++;
        $string = "";
        while ($this->pos < strlen($this->source) && $this->char() != $opening_quote) {
            $string .= $this->char();
            $this->pos++;
        }
        $this->pos++; 
        $this->push_token("STRING", $string, $start, $this->pos-1);
    }
}

class Program {
    public $body = [];
}

class EchoStatement {
    public $expression;
    public $pos;

    public function __construct($expression, $pos) {
        $this->expression = $expression;
        $this->pos = $pos;
    }
}


class DeclareVariable {
    public $name;
    public $value;
    public $pos; 

    public function __construct($name, $value, $pos) {
        $this->name = $name;
        $this->value = $value;
        $this->pos = $pos;
    }
}

class DeclareFunction {
    public $name;
    public $args;
    public $body;

    public function __construct($name, $args, $body) {
        $this->name = $name;
        $this->args = $args;
        $this->body = $body;
    }
}

class IfStatement {
    public $condition;
    public $body = [];
    public $tryother = [];
    public $else;
    public $pos; 

    public function __construct($condition, $body, $tryother, $else, $pos) {
        $this->condition = $condition;
        $this->body = $body;
        $this->pos = $pos;
        $this->tryother = $tryother;
        $this->else = $else;
    }
}

class NumberLiteral {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }
}

class StringLiteral {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }
}

class Identifier {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }
}

class BinaryOperation {
    public $right;
    public $operator;
    public $left;

    public function __construct($right, $operator, $left) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
    }
}
enum Comparison
{
    case Gt;
    case Lt;
    case LtE;
    case GtE;
    case Is;
    case IsNot;
}

enum Unary
{
    case Not;
    case Minus;
}
enum Operation
{
    case Add;
    case Sub;
    case Div;
    case Mult;
}

class Compare {
    public $right;
    public $operator;
    public $left;

    public function __construct($right, $operator, $left) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
    }
}

class UnaryOperation {
    public $operator;
    public $operand;

    public function __construct($operator, $operand) {
        $this->operator = $operator;
        $this->left = $operand;
    }
}



class Parser {
    public $tokens;
    public $pos = 0;
    private $precedence = [
        '+' => 1,
        '-' => 1,
        '*' => 2,
        '/' => 2,
        '==' => 3,
        '!=' => 3,
        '<' => 3,
        '>' => 3,
        '<=' => 3,
        '>=' => 3,
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
    ];

    private $unary = [
        '!' => Unary::Not,
        '-' => Unary::Minus,
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
            echo ("Unexpected token: " . $token->type . " " . $token->value);
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
            }
        }

        return $program;
    }

    private function parse_statement() {
        $token = $this->currentToken();
    
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
            }
        }
    
        return $this->parse_expression();
    }

    private function parse_function_defination() {
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
        return new DeclareFunction($name, $args, $body);
    }

    private function parse_return_statement() {
        $this->expect('KEYWORD', 'return');
        $value = $this->parse_expression();
        $this->expect('SEMI_COLON');
        
        return new ReturnStatement($value, $this->currentToken()->position);
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
    
    
    private function parse_if_statement() {
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
        
        if ($this->currentToken()->type === 'KEYWORD' && $this->currentToken()->value === 'elif') {
            $tryother[] = $this->parse_if_statement();
        } elseif ($this->currentToken()->type === 'KEYWORD' && $this->currentToken()->value === 'else') {
            $this->expect('KEYWORD', 'else');
            $this->expect('OPEN_CURLY');
            
            $else = [];
            while ($this->currentToken()->type !== 'CLOSE_CURLY') {
                $else[] = $this->parse_statement();
            }
            $this->expect('CLOSE_CURLY');
        }
        
        return new IfStatement($condition, $body, $tryother, $else, $this->currentToken()->position);
    }


    

    private function parse_echo() {
        $this->expect('KEYWORD', 'echo');

        $expression = $this->parse_expression();
        $token = $this->currentToken();

        $echo_statement = new EchoStatement($expression, $token->position);
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
        }
        if ($token->type === 'STRING') {
            $value = $token->value;
            $this->nextToken();
            return new StringLiteral($value);
        } elseif ($token->type === 'NUMBER') {
            $value = $token->value;
            $this->nextToken();
            return new NumberLiteral($value);
        } elseif ($token->type === 'IDENTIFIER') {
            $value = $token->value;
            $this->nextToken();
            return new Identifier($value);
        } elseif ($token->type === 'OPEN_PAREN') {
            $this->nextToken();
            $expression = $this->parse_expression();
            $this->expect('CLOSE_PAREN');
            return $expression;
        }

        echo "Unexpected token type: " . $token->type . '\n';
        die;
    }
}





$lexer = new Lexer("
def my_function(a, b, c) {
    echo -a;
}
");

$parser = new Parser($lexer);
print_r($parser->parse());