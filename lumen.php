<?php

$keywords = ['let', 'if', 'else', 'elif', 'del', 'die', 'loop', 'none', 'def', 'return', 'echo', 'break', 'continue', 'set', 'import'];

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
    private $mode = "text";

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
            if ($this->mode == "lumen") {
                if (substr($this->source, $this->pos, 2) == "?>") {
                    $this->pos+=2;
                    $this->mode = "text";
                } elseif (is_numeric($char)) {
                    $this->lex_number();
                } elseif (in_array(strtolower($char), range('a', 'z'))) {
                    $this->lex_identifier();
                } elseif ($char == '"' || $char == "'") {
                    $this->lex_string();
                } elseif ($char == '=') {
                    if ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("OPERATOR", "==");
                    } else {
                        $this->push_token("EQUAL", $this->char());
                        $this->pos++;
                    }
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
                } elseif ($char == '[') {
                    $this->push_token("OPEN_BRACKET", $this->char());
                    $this->pos++;
                } elseif ($char == '~') {
                    $this->push_token("NEGATION", $this->char());
                    $this->pos++;
                } elseif ($char == ']') {
                    $this->push_token("CLOSE_BRACKET", $this->char());
                    $this->pos++;
                } elseif ($char == '%') {
                    if ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("ASSIGN_OPERATOR", "%=");
                    } else {
                        $this->push_token("OPERATOR", $this->char());
                        $this->pos++;
                    }
                } elseif ($char == ';') {
                    $this->push_token("SEMI_COLON", $this->char());
                    $this->pos++;
                } elseif ($char == ':') {
                    $this->push_token("COLON", $this->char());
                    $this->pos++;
                } elseif ($char == '$') {
                    $this->push_token("DOLLAR_SIGN", $this->char());
                    $this->pos++;
                } elseif ($char == '?') {
                    $this->push_token("QUESTION_MARK", $this->char());
                    $this->pos++;
                }elseif ($char == '+') {
                    if ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("ASSIGN_OPERATOR", "+=");
                    } else {
                        $this->push_token("OPERATOR", $this->char());
                        $this->pos++;
                    }
                }elseif ($char == '-') {
                    if ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("ASSIGN_OPERATOR", "-=");
                    } else {
                        $this->push_token("OPERATOR", $this->char());
                        $this->pos++;
                    }
                }elseif ($char == '*') {
                    if ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("ASSIGN_OPERATOR", "*=");
                    } else {
                        $this->push_token("OPERATOR", $this->char());
                        $this->pos++;
                    }
                }elseif ($char == '/') {
                    if ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("ASSIGN_OPERATOR", "/=");
                    } else {
                        $this->push_token("OPERATOR", $this->char());
                        $this->pos++;
                    }
                } elseif ($char == '!') {
                    if ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("OPERATOR", "!=");
                    } else {
                        $this->push_token("NOT", $this->char());
                        $this->pos++;
                    }
                }
                elseif ($char == '&') {
                    if ($this->source[$this->pos + 1] == '&') {
                        $this->pos+=2;
                        $this->push_token("OPERATOR", "&&");
                    } 
                    elseif ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("ASSIGN_OPERATOR", "&=");
                    }
                    else {
                        $this->push_token("OPERATOR", $this->char());
                        $this->pos++;
                    }
                }
                elseif ($char == '|') {
                    if ($this->source[$this->pos + 1] == '|') {
                        $this->pos+=2;
                        $this->push_token("OPERATOR", "||");
                    } elseif ($this->source[$this->pos + 1] == '=') {
                        $this->pos+=2;
                        $this->push_token("ASSIGN_OPERATOR", "|=");
                    } else {
                        $this->push_token("OPERATOR", $this->char());
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
                }
                
                else {
                    $this->pos++;
                }
            } else {
                $text = "";
                while ($this->pos < strlen($this->source) ) {
                    if (substr($this->source, $this->pos, 7) == "<?lumen") {
                        $this->mode = "lumen";
                        $this->pos += 7;
                        break;
                    }
                    $text .= $this->char();
                    $this->pos++;
                }
                $this->push_token("TEXT", $text);
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
    public $id;

    public function __construct($expression, $pos, $id) {
        $this->expression = $expression;
        $this->pos = $pos;
        $this->id = $id;
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

class AugAssign {
    public $name;
    public $operator; 
    public $value;

    public function __construct($name, $operator, $value) {
        $this->name = $name;
        $this->value = $value;
        $this->operator = $operator;
    }
}

class DeleteVariable {
    public $name;

    public function __construct($name) {
        $this->name = $name;
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

class FunctionCall {
    public $name;
    public $args;

    public function __construct($name, $args) {
        $this->name = $name;
        $this->args = $args;
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

class Import {
    public $filepath;

    public function __construct($filepath) {
        $this->filepath = $filepath;
    }
}

class ReturnStatement {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }
}
class Kill {}
class None {}
class BreakLoop {}
class ContinueLoop {}

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

class BoolOp {
    public $right;
    public $operator;
    public $left;

    public function __construct($right, $operator, $left) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
    }
}

class Subscript {
    public $identifier;
    public $slice;

    public function __construct($value, $slice) {
        $this->identifier = $value;
        $this->slice = $slice;
    }
}

class Slice {
    public $lower;
    public $upper;
    public $steps;

    public function __construct($lower=null, $upper=null, $steps=null) {
        $this->lower = $lower;
        $this->upper = $upper;
        $this->steps = $steps;
    }
}

class Index {
    public $value;

    public function __construct($value) {
        $this->value = $value;
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
    case Negate;
}
enum Operation
{
    case Add;
    case Sub;
    case Div;
    case Mult;
    case Modulo;
    case BitAnd;
    case BitOr;
}

enum BoolOperand
{
    case And;
    case Or;
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

class LoopStatement {
    public $condition;
    public $body;
    public $pos;
    public function __construct($condition, $body, $pos) {
        $this->condition = $condition;
        $this->body = $body;
        $this->pos = $pos;
    }
}

class AssignVariable {
    public $name;
    public $value;
    public function __construct($name, $value) {
        $this->name = $name;
        $this->value = $value;
    }
}
class LiteralText {
    public $value;
    public function __construct($value) {
        $this->value = $value;
    }
}

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
            echo ("Unexpected token: " . $token->type . " " . $token->value );
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
        if ($token->type === 'IDENTIFIER' && $this->tokens[$this->pos + 1]->type === 'EQUAL') {
            return $this->parse_set_statement();
        } elseif ($token->type === 'IDENTIFIER' && $this->tokens[$this->pos + 1]->type === 'ASSIGN_OPERATOR') {
            return $this->parse_argumented_assign();
        }

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
                case 'die':
                    return $this->parse_die();
                case 'import':
                    return $this->parse_import();
            }
        }
    
        return $this->parse_expression();
    }
    private function parse_set_statement() {
        if ($this->currentToken()->type == 'KEYWORD') {
            $this->nextToken();
        }
        $name = $this->currentToken()->value;
        $this->expect('IDENTIFIER');

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
        
        return new ReturnStatement($value);
    }

    private function parse_import() {
        $this->expect('KEYWORD', 'import');
        $value = $this->parse_expression();
        $this->expect('SEMI_COLON');
        
        return new Import($value);
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
            $value = $token->value;
            $this->nextToken();
            if ($this->currentToken()->type == 'OPEN_PAREN') {
                $args = [];
                $this->nextToken();
                while ($this->currentToken()->type != 'CLOSE_PAREN' && $this->currentToken()->type != "EOF") {
                    array_push($args, $this->parse_expression($this->currentToken()->value));
                    if ($this->currentToken()->type !== "COMMA" && $this->currentToken()->type !== "CLOSE_PAREN" ) {
                        echo ("Unexpected token: " . $this->currentToken()->type);
                        die;
                    }
                    if ($this->currentToken()->type === "COMMA") {
                        $this->nextToken();
                    }
                }
                $this->nextToken();
                if ($this->currentToken()->type == 'SEMI_COLON') {
                    $this->nextToken();
                }
                return new FunctionCall($value, $args);
            }
            if ($this->currentToken()->type == 'OPEN_BRACKET') {
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
                return new Subscript(new Identifier($value), new Slice($start, $stop, $step));

            }
            return new Identifier($value);
        } elseif ($token->type === 'OPEN_PAREN') {
            $this->nextToken();
            $expression = $this->parse_expression();
            $this->expect('CLOSE_PAREN');
            return $expression;
        }

        echo "Unexpected token type: " . $token->type ;
        die;
    }
}


class Optimizer {
    public $program;

    public function __construct($program) {
        $this->program = $program;
    }

    public function optimize() {
        $scope = [];
        $i = 0;
        foreach ($this->program->body as $statement) {
            if ($statement instanceof DeclareVariable) {
                array_push($scope, $statement->name);
            } elseif ($statement instanceof DeleteVariable) {
                $scope = array_diff($scope,[$statement->name]);
                
            }  elseif ($statement instanceof LoopStatement) {
                $this->program->body[$i] = $this->scoped_loop($statement);
            } elseif ($statement instanceof DeclareFunction) {
                $this->program->body[$i] = $this->scoped_loop($statement);
            } elseif ($statement instanceof IfStatement) {

                $this->program->body[$i] = $this->scoped_loop($statement);
                $ii = 0;
                foreach($statement->tryother as $elif) {
                    $this->program->body[$i]->tryother[$ii]->body = $this->scoped_loop($elif->body);
                }
                if (isset($statement->else)) {
                    $this->program->body[$i]->else = $this->scoped_loop($statement->else, TRUE);
                }

            }
            
            $i += 1;
        }
        foreach (array_reverse($scope) as $var) {
            array_push($this->program->body, new DeleteVariable($var));
        }
        return $this->program;
    }

    private function scoped_loop($statement, $is_else=FALSE) {
        $scope = [];
        $nodes = (!$is_else) ? $statement->body : $statement;
        foreach ($nodes as $statement_node) {
            if ($statement_node instanceof DeclareVariable) {
                array_push($scope, $statement_node->name);
            } elseif ($statement_node instanceof DeleteVariable) {
                $scope = array_diff($scope,[$statement_node->name]);
                
            }  
        }
        foreach (array_reverse($scope) as $var) {
            array_push($statement->body, new DeleteVariable($var));
        }
        return $statement;
    }
}

class FunctionObject {
    public $name;
    public $args;
    public $scope;
    public $body;

    public function __construct($name, $args, $body, $scope) {
        $this->name = $name;
        $this->args= $args;
        $this->body = $body;
        $this->scope = $scope;
    }
}

class StdLib {
    public function pow($base, $exponent) {
        return pow($base, $exponent);
    }

    public function Pi() {
        return pi();
    }

    public function array(...$items) {
        return $items;
    }
}

class Interpreter {
    private $program;
    private $source_code;
    public $functions = [];
    public $variables = [];
    private $internal_buffer = [];

    public function __construct($source_code) {
        $this->source_code = $source_code;
    }

    public function interpret() {
        $lexer = new Lexer($this->source_code);
        $parser = new Parser($lexer);
        $program = $parser->parse();
        $optimizer = new Optimizer($program);
        $this->program = $optimizer->optimize();

        foreach ($this->program->body as $statement) {
            $this->execute_statement($statement);
        }
        return implode('',$this->internal_buffer);
    }

    private function execute_statement($statement) {
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
            } else {
                $buffered .= "{$value}";
            }
            $buffered = str_replace('\n', PHP_EOL, $buffered);
            $this->internal_buffer[] = $buffered;
            } elseif ($statement instanceof LiteralText) {
                $this->internal_buffer[] = $statement->value;
            } elseif ($statement instanceof LoopStatement) {
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
        } elseif ($statement instanceof AssignVariable) {
            $name = $statement->name;
            if (!isset($this->variables[$name])) {
                echo "Assigning into undeclared variable.";
                die;
            }
            $this->variables[$name] = $this->evaluate_expression($statement->value);
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
        } elseif ($statement instanceof Import) {
            $source = file_get_contents($this->evaluate_expression($statement->filepath));
            $inside_interpreter = new Interpreter($source);
            $inside_interpreter->interpret();
            foreach ($inside_interpreter->variables as $key => $value) {
                $this->variables[$key] = $value;
            }
            foreach ($inside_interpreter->functions as $key => $value) {
                $this->functions[$key] = $value;
            }
        }
        elseif ($statement instanceof IfStatement) {
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
            $this->functions[$name->value] = new FunctionObject($name, $args, $body, [$this->variables, $this->functions]);

        } else {
            $this->evaluate_expression($statement);
        }
    }

    private function evaluate_expression($expression) {
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
            return $this->call_function($expression);
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

    private function call_function($stat) {
        $name = $stat->name;
        $args = $stat->args;
        $function_object = $this->functions[$name];
        if (count($args) !== count($function_object->args)) {
            echo "Incorrect number of arguments for function \"$name\".";
            die;
        }
        $val = null;
        
        $save_point_variables = $this->variables;
        $save_point_functions = $this->functions;
        
        foreach ($function_object->args as $index => $arg) {
            $this->variables[$arg->value] = $this->evaluate_expression($args[$index]);
        }

        foreach ($function_object->body as $statement) {
            if (!$statement instanceof ReturnStatement) {
                $this->execute_statement($statement);
            } else {
                $val = $this->evaluate_expression($statement->value) ?? null;
                break;
            }
        }
        foreach ($function_object->args as $index => $arg) {
            unset($this->variables[$arg->value]);
        }
        return $val;
    }
}



$interpreter = new Interpreter(file_get_contents($argv[1]));
if (isset($argv[2])) {
    file_put_contents($argv[2], ($interpreter->interpret())) or die("Unable to write the file.");

} else {
    echo ($interpreter->interpret());
}

