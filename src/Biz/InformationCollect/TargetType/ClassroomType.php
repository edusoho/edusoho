<?php

namespace Biz\InformationCollect\TargetType;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;

class ClassroomType extends TargetType
{
    public function getTargetInfo($targetIds)
    {
        $classrooms = $this->getClassroomService()->findClassroomsByIds($targetIds);
        $classrooms = ArrayToolkit::column($classrooms, 'title');

        return implode('；', $classrooms);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
