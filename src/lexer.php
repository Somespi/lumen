<?php


class Token {
    public $type;
    public $value;

    public function __construct($type = null, $value = null) {
        $this->type = $type;
        $this->value = $value;
    }
}

class Lexer {
    public $source;
    public $tokens = [];
    private $pos = 0;

    public function __construct($source) {
        $this->source = $source . ' ';
    }

    private function push_token($type, $value) {
        array_push($this->tokens, new Token($type, $value));
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
            } elseif ($char == '<') {
                $this->push_token("OPEN_ANGLE", $this->char());
                $this->pos++;
            } elseif ($char == '>') {
                $this->push_token("CLOSE_ANGLE", $this->char());
                $this->pos++;
            } elseif ($char == '/') {
                $this->push_token("BACK_SLASH", $this->char());
                $this->pos++;
            } elseif ($char == '=') {
                $this->push_token("EQUAL", $this->char());
                $this->pos++;
            } elseif ($char == '(') {
                $this->push_token("OPEN_PAREN", $this->char());
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
            } elseif (empty(trim($char))) {
                $this->pos++;
            } else {
                $this->pos++;
            }
        }
        return $this->tokens;
    }

    private function lex_number() {
        $number = $this->char();
        $has_dot = false;
        $this->pos++;
        while ($this->pos < strlen($this->source) && (is_numeric($this->char()) || $this->char() == '.')) {
            if ($this->char() == '.') {
                if ($has_dot) {
                    throw new Exception('More than one floating point in a number.');
                }
                $has_dot = true;
            }
            $number .= $this->char();
            $this->pos++;
        }
        $this->push_token("NUMBER", $number);
    }

    private function lex_identifier() {
        $identifier = $this->char();
        $this->pos++;
        $legable_chars = array_merge(range('0','9') ,range('A', 'Z'), range('a','z'), ['_']);
        while ($this->pos < strlen($this->source) && in_array($this->char(), $legable_chars)) {    
            $identifier .= $this->char();
            $this->pos++;
        }
        $this->push_token("IDENTIFIER", $identifier);
    }


    private function lex_string() {
        $opening_quote = $this->char();
        $this->pos++;
        $string = "";
        while ($this->pos < strlen($this->source) && $this->char() != $opening_quote) {
            $string .= $this->char();
            $this->pos++;
        }
        $this->pos++; // skip the closing quote.
        $this->push_token("STRING", $string);
    }
    
}


$lexer = new Lexer("
<?lumen
\$variable = \"Hello, World!\";
?>

<html>
<body>
<p> Hey! </p>
</body>
</html>
");
print_r($lexer->lex());

