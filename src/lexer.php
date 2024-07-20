<?php
class Lexer {
    public $source;
    public Cursor $cursor;
    public Diagnostic $diagnostic;
    private $tokens = [];
    private $pos = 0;
    private $mode = "text";

    public function __construct($source, $filepath = null) {
        $this->source = $source;
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
                } elseif (in_array(strtolower($char), range('a', 'z'))) {
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
}
?>