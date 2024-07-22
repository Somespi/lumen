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
        
        $lines = explode(PHP_EOL, $cursor->source);
        $last_line = $lines[$cursor->line - 1];
        $before_line = $lines[$cursor->line - 2] ?? "";
        $after_line = $lines[$cursor->line] ??"";
        return $before_line . PHP_EOL . $last_line . PHP_EOL . (str_repeat("^", strlen($last_line))) . PHP_EOL . $after_line . PHP_EOL;
    }
}

