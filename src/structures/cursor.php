<?php

class Cursor {
    public $source;
    public $pos = 0;
    public $line = 1;
    public $column = 1;

    public function __construct($source, $filepath = null) {
        $this->source = $source;
        $this->filepath = $filepath;
    }

    public function char() {
        return $this->source[$this->pos];
    }   


    public function goto($pos) {
        $steps = $pos - $this->pos;
        $this->next($steps);
    }
    public function next($steps = 1) {
        for ($i = 1; $i <= $steps; $i++) {
            $this->pos++;
            if ($this->pos < strlen($this->source)) {
            if ($this->source[$this->pos] == "\n") {
                $this->line++;
                $this->column = 1;
            } else {
                $this->column++;
            }
        }
        }
    }

}
