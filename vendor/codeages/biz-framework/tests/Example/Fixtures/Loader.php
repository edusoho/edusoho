<?php

namespace Tests\Example\Fixtures;

class Loader
{
    public static function loadSql()
    {
        return file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'/example.sql');
    }
}
