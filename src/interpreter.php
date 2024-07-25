<?php 

class Interpreter { 
    public $cursor;
    public $diagnostic;
    public $bytecodes;
    public $nodes;

    public function __construct($parser) {
        $this->cursor = new Cursor($parser->cursor->source, $parser->cursor->filepath);
        $this->diagnostic = new Diagnostic();
        $this->bytecodes = [];
        $this->nodes = $parser->parse();
    }

    public function interpret() {
        foreach ($this->nodes as $node) {
            $this->bytecodes[] = $this->interpret_statement();
        }
        return $this->bytecodes;
    }

}