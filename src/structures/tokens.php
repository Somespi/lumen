<?php

$keywords = ['let', 'if', 'else', 'elif', 'del',
            'die', 'loop', 'none', 'def',
            'return', 'echo', 'break', 'class', 
            'continue', 'set', 'include', 'import'];

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

