<?php

foreach (array(
    __DIR__.'/../../autoload.php',  // composer dependency
    __DIR__.'/vendor/autoload.php', // stand-alone package
) as $file) {
    if (is_file($file)) {
        return require_once $file;
    }
}
