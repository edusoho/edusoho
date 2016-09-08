<?php

namespace Topxia\Service\Importer;

use Topxia\Common\JoinPointToolkit;
use Topxia\Service\Common\NotFoundException;

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
            throw new NotFoundException(self::getKernel()->trans('UNKNOWN IMPORTER TYPE: %importerType%', array('%importerType%' => $importerType)));
        }

        $class = $map[$importerType];
        return new $class();
    }

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }
}
