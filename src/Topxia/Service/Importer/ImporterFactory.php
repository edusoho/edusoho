<?php


namespace Topxia\Service\Importer;


class ImporterFactory
{
    public static function create($importerType)
    {
        $map = array(
            'user' => 'Topxia\\Service\\Importer\\UserImporter',
        );
        $class = $map[$importerType];
        return new $class();
    }
}