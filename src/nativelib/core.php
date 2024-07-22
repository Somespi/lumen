<?php

class NativeLib
{
    public $arrayOp = null;
    public $math = null;
    public $std = null;

    public function __construct() {
        $this->arrayOp = ArrayOp::class;
        $this->math = Math::class;
        $this->std = STD::class; // No. This doesn't mean what you want it to mean.
    }

    public function __call($method, $args) {
        if (method_exists($this->std, $method)) {
            return call_user_func_array([new $this->std, $method], $args);
        } else if (method_exists($this->math, $method)) {
            return call_user_func_array([new $this->math, $method], $args);
        } else if (method_exists($this->arrayOp, $method)) {
            return call_user_func_array([new $this->arrayOp, $method], $args);
        }

        return null;

    }
    public function has($name) {
        return method_exists($this->std, $name) || method_exists($this->arrayOp, $name) || method_exists($this->math, $name);
    }
}