<?php

namespace Biz\Importer;

use AppBundle\Common\JoinPointToolkit;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class ImporterFactory
{
    public static function create($importerType)
    {
        // $map = array(
        //     'user'             => 'Biz\\Importer\\UserImporter',
        //     'course-member'    => 'Biz\\Importer\\CourseMemberImporter',
        //     'classroom-member' => 'Biz\\Importer\\ClassroomMemberImporter',
        //     'vip'              => 'Biz\\Importer\\VipImporter'
        // );

        $map = JoinPointToolkit::load('importer');

        if (!array_key_exists($importerType, $map)) {
            throw new InvalidArgumentException(sprintf('Unknown importer type: %s', $importerType));
        }

        $class = $map[$importerType];

        return new $class();
    }
}
