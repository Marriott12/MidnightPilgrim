<?php
$path = $argv[1] ?? 'app/Models/Quote.php';
$src = file_get_contents($path);
$tokens = token_get_all($src);
$classes = [];
for ($i = 0; $i < count($tokens); $i++) {
    if (is_array($tokens[$i]) && $tokens[$i][0] === T_CLASS) {
        $j = $i + 1;
        while (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
            $j++;
        }
        if (isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
            $classes[] = $tokens[$j][1];
        }
    }
}
foreach ($classes as $c) {
    echo $c . PHP_EOL;
}
