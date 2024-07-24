<?php

class STD extends NativeLib {

    public function print($value) {
        echo $value;
    }

    public function println($value) {
        echo $value . PHP_EOL;
    }

    public function die($value) {
        echo $value;
        die;
    }

    public function concat($leftOperand, $rightOperand) {
        return "$leftOperand$rightOperand";
    }

    public function index($value, $index) {
        return $value[intval($index)];
    }

    public function strlen($value) {
        return strlen($value);
    }

    public function type($value) {
        return $value->name->value;
    }

    public function ord($value) {
        return ord($value);
    }

}
