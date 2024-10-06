<?php
function toFile($filename, $data)
{
    if (function_exists('file_put_contents')) {
        file_put_contents($filename, $data);
    } else {
        $file = fopen($filename, 'wb');
        fwrite($file, $data);
        fclose($file);
    }
}

if (function_exists('debug_backtrace')) {
    $backtrace = debug_backtrace();
} else {
    $backtrace = false;
}

debug_print_backtrace();
?>