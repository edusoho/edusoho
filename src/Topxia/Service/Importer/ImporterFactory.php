<?php


namespace Topxia\Service\Importer;


class ImporterFactory
{
    public static function create($importerType)
    {
        $class = __NAMESPACE__ . '\\' . ucfirst($importerType). 'Importer';

        return new $class();
    }
}