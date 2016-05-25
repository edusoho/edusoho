<?php


namespace Topxia\Service\Checker;


class CheckerFactory
{
    public static function create($checkerType)
    {
        $class = __NAMESPACE__ . '\\' . ucfirst($checkerType). 'Checker';

        return new $class();
    }
}