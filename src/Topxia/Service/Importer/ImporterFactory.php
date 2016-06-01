<?php


namespace Topxia\Service\Importer;


class ImporterFactory
{
    public static function create($importerType)
    {
        $map = array(
            'user' => 'Topxia\\Service\\Importer\\UserImporter',
            'course-user' => 'Topxia\\Service\\Importer\\CourseUserImporter'
        );
        $class = $map[$importerType];
        return new $class();
    }
}