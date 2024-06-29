<?php

$keywords = ['let', 'if', 'else', 'elif', 'die', 'loop', 'def', 'return', 'echo' ];

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

class Kill {
    
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
                case 'die':
                    return $this->parse_die();
            }
        }
    
        return $this->parse_expression();
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

class Interpreter {
    private $program;
    private $source_code;
    private $functions = [];
    private $variables = [];
    private $echos = [];
    private $echos_logged = 0;
    private $data;
    private $echo_tracker = 0;

    public function __construct($source_code) {
        $this->source_code = $source_code;
    }

    public function interpret() {
        $pattern = "/<\?lumen([\s\S]*?)\?>/";
        $data = [];
        $collected_code = "";
        if (preg_match_all($pattern, $this->source_code, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $matchedString = $match[0];
                $startPosition = $match[1];
                $endPosition = $startPosition + strlen($matchedString);
                $collected_code .= substr($matchedString, 7, -2) . PHP_EOL;
                preg_match_all("/(?:echo [^;]*;)/", $matchedString, $number_of_echos, PREG_OFFSET_CAPTURE);
                array_push($data, [$matchedString, $startPosition, $endPosition, count($number_of_echos) ]);
                
            }
        
        $lexer = new Lexer($collected_code);
        $parser = new Parser($lexer);
        $this->program = $parser->parse();
        $this->data = $data;

        foreach ($this->program->body as $statement) {
            $this->execute_statement($statement);
        }
        
        
        foreach (($this->data) as $dataItem) {
            $startPosition = $dataItem[1];
            $endPosition = $dataItem[2];
            $replacement = "";
            $loop = $this->echos[$startPosition] ?? [];
            foreach ($loop as $value) {
                $replacement .= "{$value}";
            }
            $escaped_pattern = preg_quote($dataItem[0], '/');
            $this->source_code = preg_replace("/{$escaped_pattern}/", $replacement, $this->source_code);

        }
    }
    return $this->source_code;
    }

    

    private function execute_statement($statement) {
        if ($statement instanceof Kill) {
            die;
        } elseif ($statement instanceof DeclareVariable) {
            if (array_key_exists($statement->name, $this->variables)) {
                echo "Variable \"" . $statement->name ."\" already exists.";
                die;
            }
            $this->variables[$statement->name] = $this->evaluate_expression($statement->value);
        } elseif ($statement instanceof EchoStatement) {
            $value = $this->evaluate_expression($statement->expression);
            $echo_data = $this->data[$this->echo_tracker];
            $start_value = $echo_data[1];
            if (isset($this->echos[$start_value])) {
                    array_push($this->echos[$start_value], $value);
            } else {
                $this->echos[$start_value] = [$value];
            }
            $this->echos_logged += 1;
            if ($echo_data[3] == $this->echos_logged) {
                $this->echo_tracker++;
            }

        } else {
            $this->evaluate_expression($statement);
        }
    }

    private function evaluate_expression($expression) {
        if ($expression instanceof NumberLiteral) {
            return (float)$expression->value;
        } 
        if ($expression instanceof StringLiteral) {
            return $expression->value;
        } 
        if ($expression instanceof Identifier) {
            if (!array_key_exists($expression->value, $this->variables)) {
                echo "Undefined identifier \"" . $expression->value ."\".";
                die;
            }
            return $variables[$expression->value];
        }
        if ($expression instanceof BinaryOperation) {
            $left = $this->evaluate_expression($expression->left);
            $right = $this->evaluate_expression($expression->right);
            switch ($expression->operator) {
                case Operation::Add:
                    return $left + $right;
                case Operation::Sub:
                    return $left - $right;
                case Operation::Div:
                    return $left / $right;
                case Operation::Mult:
                    return $left * $right;
            }
        }
        if ($expression instanceof Compare) {
            $left = $this->evaluate_expression($expression->left);
            $right = $this->evaluate_expression($expression->right);
            switch ($expression->operator) {
                case Comparison::Gt:
                    return $left > $right;
                case Comparison::Lt:
                    return $left < $right;
                case Comparison::LtE:
                    return $left <= $right;
                case Comparison::GtE:
                    return $left >= $right;
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
                    return !($operand);
                case Unary::Minus:
                    return -($operand);
            }
        }

        
    }
}



$source = "
<?lumen echo 'Hey'; ?>
<p>
<?lumen 
    echo 34;    
    echo ' LOL';
?>
</p>
";
$interpreter = new Interpreter($source);
echo ($interpreter->interpret());