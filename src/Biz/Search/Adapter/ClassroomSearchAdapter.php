<?php

namespace Biz\Search\Adapter;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomService;

class ClassroomSearchAdapter extends AbstractSearchAdapter
{
    public function adapt(array $classrooms)
    {
        $adaptResult = array();
        $user = $this->getCurrentUser();

        $memberClassroomIds = array();

        if (!empty($user['id'])) {
            $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');
            $plans = $this->getClassroomService()->findClassroomsByIds($classroomIds);
            $planIds = ArrayToolkit::column($plans, 'id');
            $memberClassrooms = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user['id'], $planIds);
            $memberClassroomIds = ArrayToolkit::column($memberClassrooms, 'classroomId');
        }

        foreach ($classrooms as $index => $classroom) {
            $classroom = $this->adaptClassroom($classroom, $memberClassroomIds);

            array_push($adaptResult, $classroom);
        }

        return $adaptResult;
    }

    protected function adaptClassroom($classroom, $learningClassroomIds)
    {
        $classroomLocal = $this->getClassroomService()->getClassroom($classroom['classroomId']);

        if (!empty($classroomLocal)) {
            $classroom['id'] = $classroomLocal['id'];
            $classroom['rating'] = $classroomLocal['rating'];
            $classroom['ratingNum'] = $classroomLocal['ratingNum'];
            $classroom['studentNum'] = $classroomLocal['studentNum'];
            $classroom['middlePicture'] = $classroomLocal['middlePicture'];
            $classroom['learning'] = in_array($classroom['classroomId'], $learningClassroomIds);
        } else {
            $classroom['rating'] = 0;
            $classroom['ratingNum'] = 0;
            $classroom['studentNum'] = 0;
            $classroom['middlePicture'] = '';
            $classroom['learning'] = false;
            $classroom['id'] = $classroom['classroomId'];
        }

        return $classroom;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
