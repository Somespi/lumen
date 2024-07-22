<?php

class ArrayOp {
    public function array(...$items) {
        return $items;
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
}
