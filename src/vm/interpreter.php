<?php 

class Interpreter { 
    
    public $stack = [];
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
            $this->bytecodes[] = $this->interpret_statement($node);
        }
        return $this->bytecodes;
    }

    private function interpret_statement($statement) {
        if ($statement instanceof Kill) {
            return Bytecode::Die;
        } else {
            return $statement->execute();
        }
    }

}