<?php
xdebug_start_trace();

function test_extensions()
{
    $image = imagecreate(320, 240);
    imageantialias($image, true);
    return $image;
}

print_r(apache_get_modules());

if (!extension_loaded('SQLite')) {
    $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
    dl($prefix . 'sqlite.' . PHP_SHLIB_SUFFIX);
    echo 'SQLite version : ' . sqlite_libversion();
}

xdebug_stop_trace();
?>