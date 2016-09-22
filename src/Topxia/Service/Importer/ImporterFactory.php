<?php

namespace Topxia\Service\Importer;

use Topxia\Common\JoinPointToolkit;
use Topxia\Common\Exception\InvalidArgumentException;

class ImporterFactory
{
    public static function create($importerType)
    {
        // $map = array(
        //     'user'             => 'Topxia\\Service\\Importer\\UserImporter',
        //     'course-member'    => 'Topxia\\Service\\Importer\\CourseMemberImporter',
        //     'classroom-member' => 'Topxia\\Service\\Importer\\ClassroomMemberImporter',
        //     'vip'              => 'Topxia\\Service\\Importer\\VipImporter'
        // );

        $map = JoinPointToolkit::load('importer');

        if (!array_key_exists($importerType, $map)) {
            throw new InvalidArgumentException(sprintf('Unknown importer type: %s', $importerType));
        }

        $class = $map[$importerType];
        return new $class();
    }
}
