<?php


$interpreter = new Interpreter(file_get_contents($argv[1]), $argv[1]);
if (isset($argv[2])) {
    file_put_contents($argv[2], ($interpreter->interpret())) or die("Unable to write the file.");

} else {
    echo ($interpreter->interpret());
}

?>