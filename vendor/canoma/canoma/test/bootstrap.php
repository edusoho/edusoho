<?php


// Fixing Phake include path.
set_include_path(
    __DIR__ .'/../vendor/phake/phake/src/' .
    PATH_SEPARATOR .
    get_include_path()
);


require __DIR__ .'/../vendor/autoload.php';
