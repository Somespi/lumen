<?php

$keywords = ['let', 'if', 'else', 'elif', 'del', 'die', 'loop', 'def', 'return', 'echo', 'break', 'continue', 'set'];

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

    public function __construct($name, $args, $body) {
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

class Kill {}
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
                case 'set':
                    return $this->parse_set_statement();
                case 'del':
                    return $this->parse_del();
                case 'die':
                    return $this->parse_die();
            }
        }
    
        return $this->parse_expression();
    }
    private function parse_set_statement() {
        $this->expect('KEYWORD', 'set');
        $name = $this->currentToken()->value;
        $this->expect('IDENTIFIER');

        $this->expect('EQUAL');

        $value = $this->parse_expression();
        $token = $this->currentToken();

        $var_reassign = new AssignVariable($name, $value);
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
            if ($this->currentToken()->type == 'OPEN_PAREN') {
                $args = [];
                $this->nextToken();
                while ($this->currentToken()->type != 'CLOSE_PAREN' && $this->currentToken()->type != "EOF") {
                    array_push($args, $this->parse_expression($this->currentToken()->value));
                    $this->nextToken();
                    if ($this->currentToken()->type !== "COMMA" && $this->currentToken()->type !== "CLOSE_PAREN" ) {
                        echo ("Unexpected token: " . $this->currentToken()->type);
                        die;
                    }
                    if ($this->currentToken()->type === "COMMA") {
                        $this->nextToken();
                    }
                }
                return new FunctionCall($value, $args);
            } 
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
            }
            elseif ($statement instanceof IfStatement) {
                $this->program->body[$i] = $this->scoped_loop($statement);
                $ii = 0;
                foreach($statement->tryother as $elif) {
                    $this->program->body[i]->tryother[ii]->body = $this->scoped_loop($elif->body);
                }
                if (isset($statement->else)) {
                    $this->program->body[i]->else = $this->scoped_loop($statement->else);
                }

            }
            
            $i += 1;
        }
        foreach (array_reverse($scope) as $var) {
            array_push($this->program->body, new DeleteVariable($var));
        }
        return $this->program;
    }

    private function scoped_loop($statement) {
        $scope = [];
        foreach ($statement->body as $statement_node) {
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
                array_push($data, [$matchedString, $startPosition, $endPosition, count($number_of_echos[0]) ]);
                
            }
        
        $lexer = new Lexer($collected_code);
        $parser = new Parser($lexer);
        $program = $parser->parse();
        $optimizer = new Optimizer($program);
        $this->program = ($optimizer->optimize());
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
            while ($this->data[$this->echo_tracker][3] == 0) {
                $this->echo_tracker += 1;
            }
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

        }
        elseif ($statement instanceof AssignVariable) {
            $name = $statement->name;
            if (!isset($this->variables[$name])) {
                echo "Assigning into undeclared variable.";
                die;
            }
            $this->variables[$name] = $this->evaluate_expression($statement->value);
        }
        elseif ($statement instanceof BreakLoop) {
            echo "Use of break outside of a loop is illegal.";
            die;
        } elseif ($statement instanceof ContinueLoop) {
            echo "Use of continue outside of a loop is illegal.";
            die;
        } elseif ($statement instanceof IfStatement) {
            $condition = $this->evaluate_expression($statement->condition);
            if($condition) {
                foreach ($statement->body as $statement_child) {
                    $this->execute_statement($statement_child);
                }
            } else {
                $pass_to_else = TRUE;
                foreach ($statement->tryother as $elif) {
                    if ($this->evaluate_expression($elif->condition)) {
                        foreach ($elif->body as $statement_child) {
                            $this->execute_statement($statement_child);
                        }
                        $pass_to_else = FALSE;
                        break;
                    }
                }
                if ($pass_to_else) {
                    if (isset($statement->else)) {
                        foreach ($statement->else as $statement_child) {
                            $this->execute_statement($statement_child);
                        }
                    }
                }
            }
        } 
        elseif ($statement instanceof DeleteVariable) {
            $name = $statement->name;
            if (!isset($this->variables[$name])) {
                echo "Unexpected Identifier \"" . $name ."\" was given to del";
                die;
            }
            unset($this->variables[$name]);
        } elseif ($statement instanceof FunctionDeclare) {
            $function_name = $statement->name;
            $args = $statement->args;
            $body = $statement->body;

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
                echo "Found undeclared identifier \"" . $expression->value ."\".";
                die;
            }
            return $this->variables[$expression->value];
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
<?lumen 
let a = 34;
?>
<p>
    <center>
    <?lumen echo a; ?>
    </center>
</p>
";
$interpreter = new Interpreter($source);
echo ($interpreter->interpret());