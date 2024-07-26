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
        } 

        else if ($statement instanceof LiteralText) {
            $this->stack[] = new Atom($statement->value, AtomType::LiteralText);
            $this->bytecodes[] = Bytecode::LoadConst;
            return count($this->stack) - 1;
        }
        else if ($statement instanceof AssignVariable) {
            $name = $this->execute($statement->left);
            $value = $this->execute($statement->value);
            $this->bytecodes[] = Bytecode::StoreName; 
            return $name;
        }
        else {
            return $statement->execute(); 
        }
    }

    private function execute($node) {
        return $node->execute();
    }

}
