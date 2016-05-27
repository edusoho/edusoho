<?php


namespace Topxia\Service\Importer;


class CheckerFactory
{
    public static function create($checkerType)
    {
        $class = __NAMESPACE__ . '\\' . ucfirst($checkerType). 'Checker';

        return new $class();
    }
}