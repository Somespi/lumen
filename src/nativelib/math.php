<?php

class Math extends NativeLib {
    public function pow($base, $exponent) {
        return pow($base, $exponent);
    }

    public function Pi() {
        return pi();
    }

    public function E() {
        return exp(1);
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

}