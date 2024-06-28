<? 
function string_pop(&$str) {
    $last_char = substr($str, -1);
    $str = substr($str, 0, -1);
    return $last_char; 
}
