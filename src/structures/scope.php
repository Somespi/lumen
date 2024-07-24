<?php 

class Scope {
    public $variables = [];
    public $parent = null;
    public function __construct(Scope $parent) {
        $this->parent = $parent;
        $this->variables = $parent->variables;
    }
}
