<?php
$requirements = [
    "root" => "./src/cli.php",
    "dependencies" => [
        "./src/structures/cursor.php",
        "./src/structures/diagnostic.php",
        "./src/structures/tokens.php",
        "./src/lexer.php",
        "./src/structures/nodes.php",
        "./src/structures/stdlib.php",
        "./src/parser.php",
        "./src/optimizer.php",
        "./src/interpreter.php",
    ]
];

function loadAndStripPHP($filePath) {
    if (!file_exists($filePath)) {
        throw new Exception("File not found: $filePath");
    }
    
    $content = file_get_contents($filePath);
    $content = preg_replace('/^\s*<\?php\s*/', '', $content, 1);
    $content = preg_replace('/\s*\?>\s*$/', '', $content, 1);
    
    return $content;
}

try {
    $code = "";
    foreach ($requirements["dependencies"] as $dependency) {
        $code .= loadAndStripPHP($dependency);
    }
    $code .= loadAndStripPHP($requirements["root"]);
    file_put_contents("t.php", $code);
    eval($code);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
