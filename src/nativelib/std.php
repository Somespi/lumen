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

}
