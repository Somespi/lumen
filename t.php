class Cursor {
    public $source;
    public $pos = 0;
    public $line = 1;
    public $column = 1;

    public function __construct($source, $filepath = null) {
        $this->source = $source;
        $this->filepath = $filepath;
    }

    public function char() {
        return $this->source[$this->pos];
    }   


    public function goto($pos) {
        $steps = $pos - $this->pos;
        $this->next($steps);
    }
    public function next($steps = 1) {
        for ($i = 1; $i <= $steps; $i++) {
            $this->pos++;
            if ($this->pos < strlen($this->source)) {
            if ($this->source[$this->pos] == "\n") {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }
        }
        }
    }

}
enum ErrorType: string {

    case Unknown = "Unknown";
    case Lexical = "Lexical";
    case Syntax = "Syntax";
    case Runtime = "Runtime";
    case Import = "Import";
    case FloatingPoint = "FloatingPoint";
    case InvalidArgumentCount = "InvalidArgumentCount";
    case Identifier = "Identifier";


    public function str() {
        return $this->value;
    }
    
}

class Diagnostic {

    public $type;
    public $message;
    public $position;

    public function raise($error_type, $message, $start, $cursor) {
        error_log("File \"" . $cursor->filepath . ":" . $cursor->line . ":" . $cursor->column . "\", line " . $cursor->line . PHP_EOL);
        error_log($this->display_snippet($start, $cursor));

        error_log($error_type->value . " Error: " . $message . PHP_EOL);
        die;

    }

    public function display_snippet($start, $cursor) {
        
        $lines = explode(PHP_EOL, $cursor->source);
        $last_line = $lines[$cursor->line - 1];
        $before_line = $lines[$cursor->line - 2] ?? "";
        $after_line = $lines[$cursor->line] ??"";
        return $before_line . PHP_EOL . $last_line . PHP_EOL . (str_repeat("^", strlen($last_line))) . PHP_EOL . $after_line . PHP_EOL;
    }
}

$keywords = ['let', 'if', 'else', 'elif', 'del',
            'die', 'loop', 'none', 'def',
            'return', 'echo', 'break', 'class', 
            'continue', 'set', 'include', 'import'];

class Token {
    public $type;
    public $value; 
    public $position;
    public function __construct($type = null, $value = null, $position) {
        $this->type = $type;
        $this->value = $value;
        $this->position = $position;
    }
}class Lexer {
    public $source;
    public Cursor $cursor;
    public $filepath;
    public Diagnostic $diagnostic;
    private $tokens = [];
    private $pos = 0;
    private $mode = "text";

    public function __construct($source, $filepath = null) {
        $this->source = $source;
        $this->filepath = $filepath;
        $this->cursor = new Cursor($source, $filepath);
        $this->diagnostic = new Diagnostic();
    }

    private function push_token($type, $value, $start=null, $end=null) {
        array_push($this->tokens, new Token($type, $value, [is_null($start) ? $this->cursor->pos : $start, is_null($end) ? $this->cursor->pos : $end]));
    }


    public function lex() {
        while ($this->cursor->pos < strlen($this->cursor->source)) {
            if ($this->mode == "lumen") {
                $char = $this->cursor->char();
                if (substr($this->source, $this->cursor->pos, 2) == "?>") {
                    $this->cursor->next(2);
                    $this->mode = "text";
                    continue;
                } elseif ($char == '#') {
                    while ($this->cursor->pos < strlen($this->source) && $this->cursor->char() != "\n") {
                        $this->cursor->next();
                    }
                } elseif (is_numeric($char)) {
                    $this->lex_number();
                } elseif (in_array(strtolower($char), array_merge(range('a', 'z'), ['_']))) {
                    $this->lex_identifier();
                } elseif ($char == '"' || $char == "'") {
                    $this->lex_string();
                } elseif ($char == '=') {
                    if ($this->source[$this->cursor->pos + 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("OPERATOR", "==");
                    } else {
                        $this->push_token("EQUAL", $this->cursor->char());
                        $this->cursor->next();
                    }
                } elseif ($char == '(') {
                    $this->push_token("OPEN_PAREN", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == ')') {
                    $this->push_token("CLOSE_PAREN", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == ',') {
                    $this->push_token("COMMA", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == '{') {
                    $this->push_token("OPEN_CURLY", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == '}') {
                    $this->push_token("CLOSE_CURLY", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == '[') {
                    $this->push_token("OPEN_BRACKET", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == '~') {
                    $this->push_token("NEGATION", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == ']') {
                    $this->push_token("CLOSE_BRACKET", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == '%') {
                    if ($this->source[$this->cursor->pos+ 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("ASSIGN_OPERATOR", "%=");
                    } else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                } elseif ($char == ';') {
                    $this->push_token("SEMI_COLON", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == ':') {
                    $this->push_token("COLON", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == '$') {
                    $this->push_token("DOLLAR_SIGN", $this->cursor->char());
                    $this->cursor->next();
                } elseif ($char == '?') {
                    $this->push_token("QUESTION_MARK", $this->cursor->char());
                    $this->cursor->next();
                }elseif ($char == '+') {
                    if ($this->source[$this->cursor->pos+ 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("ASSIGN_OPERATOR", "+=");
                    } else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                }elseif ($char == '-') {
                    if ($this->source[$this->cursor->pos+ 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("ASSIGN_OPERATOR", "-=");
                    } else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                }elseif ($char == '*') {
                    if ($this->source[$this->cursor->pos+ 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("ASSIGN_OPERATOR", "*=");
                    } else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                }elseif ($char == '/') {
                    if ($this->source[$this->cursor->pos+ 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("ASSIGN_OPERATOR", "/=");
                    } else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                } elseif ($char == '!') {
                    if ($this->source[$this->cursor->pos+ 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("OPERATOR", "!=");
                    } else {
                        $this->push_token("NOT", $this->cursor->char());
                        $this->cursor->next();
                    }
                }
                elseif ($char == '&') {
                    if ($this->source[$this->cursor->pos + 1] == '&') {
                        $this->cursor->next(2);
                        $this->push_token("OPERATOR", "&&");
                    } 
                    elseif ($this->source[$this->cursor->pos + 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("ASSIGN_OPERATOR", "&=");
                    }
                    else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                }
                elseif ($char == '|') {
                    if ($this->source[$this->cursor->pos + 1] == '|') {
                        $this->cursor->next(2);
                        $this->push_token("OPERATOR", "||");
                    } elseif ($this->source[$this->cursor->pos + 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("ASSIGN_OPERATOR", "|=");
                    } else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                }
                elseif ($char == '<') {
                    if ($this->source[$this->cursor->pos + 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("OPERATOR", "<=");
                    } else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                }elseif ($char == '>') {
                    if ($this->source[$this->cursor->pos + 1] == '=') {
                        $this->cursor->next(2);
                        $this->push_token("OPERATOR", ">=");
                    } else {
                        $this->push_token("OPERATOR", $this->cursor->char());
                        $this->cursor->next();
                    }
                }
                elseif ($char == '.') {
                    $this->push_token("PERIOD", $this->cursor->char());
                    $this->cursor->next();
                }
                elseif (empty(trim($char))) {
                    $this->cursor->next();
                }
                
                else {
                    $this->cursor->next();
                    $this->diagnostic->raise(ErrorType::Syntax, "Found Unexpected token: " . $char, $this->cursor->pos-1, $this->cursor);
                }
            } else {
                $text = "";
                if ($this->cursor->pos < strlen($this->cursor->source) ) {
                while ($this->cursor->pos < strlen($this->cursor->source) ) {
                    if (substr($this->source, $this->cursor->pos,  7) == "<?lumen") {

                        $this->mode = "lumen";
                        $this->cursor->next(7);
                        break;
                    }
                    $text .= $this->cursor->char();
                    $this->cursor->next();
                }
            }
                $this->push_token("TEXT", $text);

            }
        }
        $this->push_token('EOF', '\0');
        return $this->tokens;
    }

    private function lex_number() {
        $number = $this->cursor->char();
        $start = $this->cursor->pos ;
        $has_dot = false;
        $this->cursor->next();
        while ($this->cursor->pos < strlen($this->source) && (is_numeric($this->cursor->char()) || $this->cursor->char() == '.')) {
            if ($this->cursor->char() == '.') {
                if ($has_dot) {
                    $this->diagnostic->raise(ErrorType::FloatingPoint, "More than one floating point in a number.", $this->cursor->pos  - 1, $this->cursor);
                    
                }
                $has_dot = true;
            }
            $number .= $this->cursor->char();
            $this->cursor->next();
        }
        $this->push_token("NUMBER", $number, $start, $this->cursor->pos  - 1);
    }

    private function lex_identifier() {
        $identifier = $this->cursor->char();
        $start = $this->cursor->pos  ;
        $this->cursor->next();
        $legible_chars = array_merge(range('0','9'), range('A', 'Z'), range('a','z'), ['_']);
        while ($this->cursor->pos < strlen($this->source) && in_array($this->cursor->char(), $legible_chars)) {
            $identifier .= $this->cursor->char();
            $this->cursor->next();
        }
        $this->push_token(in_array($identifier, $GLOBALS['keywords']) ? "KEYWORD" : "IDENTIFIER", $identifier,  $start, $this->cursor->pos  -1);
    }

    private function lex_string() {
        $opening_quote = $this->cursor->char();
        $start = $this->cursor->pos;
        $this->cursor->next();
        $string = "";
        while ($this->cursor->pos < strlen($this->source) && $this->cursor->char() != $opening_quote) {
            $string .= $this->cursor->char();
            $this->cursor->next();
        }
        $this->cursor->next(); 
        $this->push_token("STRING", $string, $start, $this->cursor->pos - 1);
    }
}class Program {
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

class AugAssign {
    public $name;
    public $operator; 
    public $value;
    public $pos;

    public function __construct($name, $operator, $value, $pos) {
        $this->name = $name;
        $this->value = $value;
        $this->operator = $operator;
        $this->pos = $pos;
    }
}

class DeleteVariable {
    public $name;
    public $pos;

    public function __construct($name, $pos) {
        $this->name = $name;
        $this->pos = $pos;
    }
}

class ObjectDeclare {
    public $name;
    public $body = [];
    public $pos;

    public function __construct($name, $body, $pos) {
        $this->name = $name;
        $this->body = $body;
        $this->pos = $pos;
    }
}

class DeclareFunction {
    public $name;
    public $args;
    public $body;
    public $pos;

    public function __construct($name, $args, $body, $pos) {
        $this->name = $name;
        $this->args = $args;
        $this->body = $body;
        $this->pos = $pos;
    }
}

class MemberAccess {
    public $object;
    public $property;
    public $pos;

    public function __construct($object, $property, $pos) {
        $this->object = $object;
        $this->property = $property;
        $this->pos = $pos;
    }
}

class FunctionCall {
    public $name;
    public $args;
    public $pos;

    public function __construct($name, $args, $pos) {
        $this->name = $name;
        $this->args = $args;
        $this->pos = $pos;
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
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class StringLiteral {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class Identifier {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class IncludeStatement {
    public $filepath;
    public $pos;

    public function __construct($filepath, $pos) {
        $this->filepath = $filepath;
        $this->pos = $pos;
    }
}

class ImportStatement {
    public $filepath;
    public $pos;

    public function __construct($filepath, $pos) {
        $this->filepath = $filepath;
        $this->pos = $pos;
    }
}

class ReturnStatement {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class Kill {
    public $pos;

    public function __construct($pos) {
        $this->pos = $pos;
    }
}

class None {
    public $pos;

    public function __construct($pos) {
        $this->pos = $pos;
    }
}

class BreakLoop {
    public $pos;

    public function __construct($pos) {
        $this->pos = $pos;
    }
}

class ContinueLoop {
    public $pos;

    public function __construct($pos) {
        $this->pos = $pos;
    }
}

class BinaryOperation {
    public $right;
    public $operator;
    public $left;
    public $pos;

    public function __construct($right, $operator, $left, $pos) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
        $this->pos = $pos;
    }
}

class BoolOp {
    public $right;
    public $operator;
    public $left;
    public $pos;

    public function __construct($right, $operator, $left, $pos) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
        $this->pos = $pos;
    }
}

class Subscript {
    public $identifier;
    public $slice;
    public $pos;

    public function __construct($value, $slice, $pos) {
        $this->identifier = $value;
        $this->slice = $slice;
        $this->pos = $pos;
    }
}

class Slice {
    public $lower;
    public $upper;
    public $steps;
    public $pos;

    public function __construct($lower=null, $upper=null, $steps=null, $pos) {
        $this->lower = $lower;
        $this->upper = $upper;
        $this->steps = $steps;
        $this->pos = $pos;
    }
}

class Index {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
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
    public $pos;

    public function __construct($right, $operator, $left, $pos) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
        $this->pos = $pos;
    }
}

class UnaryOperation {
    public $operator;
    public $operand;
    public $pos;

    public function __construct($operator, $operand, $pos) {
        $this->operator = $operator;
        $this->operand = $operand;
        $this->pos = $pos;
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
    public $left;
    public $value;
    public $pos;

    public function __construct($name, $value, $pos) {
        $this->left = $name;
        $this->value = $value;
        $this->pos = $pos;
    }
}

class LiteralText {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class FunctionObject {
    public $name;
    public $args;
    public $scope;
    public $body;
    public $pos;

    public function __construct($name, $args, $body, $scope, $pos) {
        $this->name = $name;
        $this->args = $args;
        $this->body = $body;
        $this->scope = $scope;
        $this->pos = $pos;
    }
}

class ObjectType {
    public $name;
    public $properties;
    public $interpreter;
    public $pos;

    public function __construct($name, $properties, $pos, $interpreter = null) {
        $this->name = $name;
        $this->properties = $properties;
        $this->interpreter = $interpreter;
        $this->pos = $pos;
    }
}

class StdLib {
    public function pow($base, $exponent) {
        return pow($base, $exponent);
    }

    public function Pi() {
        return pi();
    }

    public function E() {
        return exp(1);
    }
    public function array(...$items) {
        return $items;
    }

    public function len(...$items) {
        return count($items[0]);
    }

    public function range($start, $stop, $step = 1) {
        return range($start, $stop, $step);
    }

    public function sum(...$items) {
        return array_sum($items[0]);
    }

    public function min(...$items) {
        return min($items[0]);
    }

    public function max(...$items) {
        return max($items[0]);
    }

    public function abs($value) {
        return abs($value);
    }

    public function floor($value) {
        return floor($value);
    }

    public function ceil($value) {
        return ceil($value);
    }  

    public function round($value) {
        return round($value);
    }

    public function sqrt($value) {
        return sqrt($value);
    }

    public function sin($value) {
        return sin($value);
    }

    public function cos($value) {
        return cos($value);
    }

    public function tan($value) {
        return tan($value);
    }

    public function asin($value) {
        return asin($value);
    }   

    public function acos($value) {
        return acos($value);
    }

    public function atan($value) {
        return atan($value);
    }

    public function print($value) {
        echo $value;
    }

    public function println($value) {
        echo $value . PHP_EOL;
    }

    public function die($value) {
        echo $value;
        die;
    }

}class Parser {
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

                if ($start == TRUE) {
                    $internal_parser = new Parser(new Lexer(""));
                    $internal_parser->tokens = array_merge(
                        array_slice($this->tokens , $start_not[0][0], $start_not[0][1] - $start_not[0][0]), 
                        [new Token("EOF", '\0', [$start_not[0][1] - $start_not[0][0], $start_not[0][1] - $start_not[0][0]])]
                    );
                    $internal_program = $internal_parser->parse();
                    $start = $internal_program->body[0]; 
                } else {
                    $start = new NumberLiteral('0', $token->position);
                }

                if ($stop == TRUE) {
                    $internal_parser = new Parser(new Lexer(""));
                    $internal_parser->tokens = array_merge(
                        array_slice($this->tokens , $start_not[1][0] + 1, $start_not[1][1] - ($start_not[1][0] + 1)), 
                        [new Token("EOF", '\0', [$start_not[1][1] - $start_not[1][0], $start_not[1][1] - $start_not[1][0] ])]
                    );
                    if (count($internal_parser->tokens) == 1) {
                        $stop = new UnaryOperation(Unary::Minus, new NumberLiteral('1', $token->position), $token->position);
                    }
                    else {
                        $internal_program = $internal_parser->parse();
                        $stop = $internal_program->body[0];
                    }
                } else {
                    $stop = new UnaryOperation(Unary::Minus, new NumberLiteral('1', $token->position), $token->position);
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
                    $step = new NumberLiteral('1', $token->position);
                }
                $sl_op = $this->currentToken()->position[1];
                $this->nextToken();
                $ident = new Subscript(new Identifier($value, $token->position), new Slice($start, $stop, $step, [$sl_rt, $sl_op]), [$ident->pos[0], $this->currentToken()->position[1]]);

            }
            
            return $ident;
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
                $scope = array_diff($scope,[$statement->name->value]);
                
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
            array_push($this->program->body, new DeleteVariable($var, null));
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
            array_push($statement->body, new DeleteVariable($var, null));
        }
        return $statement;
    }
}class Interpreter {
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

$interpreter = new Interpreter(file_get_contents($argv[1]), $argv[1]);
if (isset($argv[2])) {
    file_put_contents($argv[2], ($interpreter->interpret())) or die("Unable to write the file.");

} else {
    echo ($interpreter->interpret());
}