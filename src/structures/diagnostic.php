<?php 

enum ErrorType: string {

    case Unknown = "Unknown";
    case Lexical = "Lexical";
    case Syntax = "Syntax";
    case Runtime = "Runtime";
    case FloatingPoint = "Floating Point";
    case UndeclaredIdentifier = "Undeclared Identifier";


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

        $start = $start;
        $end = $cursor->pos;
        $snippet = substr($cursor->source, $start, $end - $start);

        return $snippet . PHP_EOL . (str_repeat("^", $cursor->column - 1)) . PHP_EOL;
    }
}

?>