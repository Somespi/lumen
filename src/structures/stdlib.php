<?php

class StdLib {
    public function pow($base, $exponent) {
        return pow($base, $exponent);
    }

    public function Pi() {
        return pi();
    }

    public function E() {
        return exp(1);
    }
    public function array(...$items) {
        return $items;
    }

    public function len(...$items) {
        return count($items[0]);
    }

    public function range($start, $stop, $step = 1) {
        return range($start, $stop, $step);
    }

    public function sum(...$items) {
        return array_sum($items[0]);
    }

    public function min(...$items) {
        return min($items[0]);
    }

    public function max(...$items) {
        return max($items[0]);
    }

    public function abs($value) {
        return abs($value);
    }

    public function floor($value) {
        return floor($value);
    }

    public function ceil($value) {
        return ceil($value);
    }  

    public function round($value) {
        return round($value);
    }

    public function sqrt($value) {
        return sqrt($value);
    }

    public function sin($value) {
        return sin($value);
    }

    public function cos($value) {
        return cos($value);
    }

    public function tan($value) {
        return tan($value);
    }

    public function asin($value) {
        return asin($value);
    }   

    public function acos($value) {
        return acos($value);
    }

    public function atan($value) {
        return atan($value);
    }

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

}
?>