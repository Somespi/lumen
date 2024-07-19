<?php
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
                } elseif ($char == '#') {
                    while ($this->pos < strlen($this->source) && $this->char() != "\n") {
                        $this->pos++;
                    }
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
                }
                elseif ($char == '.') {
                    $this->push_token("PERIOD", $this->char());
                    $this->pos++;
                }
                elseif (empty(trim($char))) {
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
?>