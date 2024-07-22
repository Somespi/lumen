<?php 

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

        $end = $cursor->pos;
        $lines = explode(PHP_EOL, substr($cursor->source, 0, $start));

        return $lines[count($lines) - 1] . PHP_EOL . (str_repeat("^", $cursor->column - 1)) . PHP_EOL;
    }
}

