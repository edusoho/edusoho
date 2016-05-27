<?php


namespace Topxia\Service\Importer;


class CheckerFactory
{
    public static function create($checkerType)
    {
        $map = array(
            'user' => 'Topxia\\Service\\Importer\\UserChecker',
        );
        $class = $map[$checkerType];
        return new $class();
    }
}