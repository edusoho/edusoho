<?php

namespace ApiBundle\Api\Resource\SignedContract;

use Biz\Classroom\Service\ClassroomService;
use Biz\Contract\Service\ContractService;
use Biz\Course\Service\CourseService;

trait SignedContractWrapTrait
{
    private function getGoodsName($goodsType, $targetId)
    {
        if ('course' == $goodsType) {
            $course = $this->getCourseService()->getCourse($targetId);

            return "{$course['courseSetTitle']}-{$course['title']}";
        }
        if ('classroom' == $goodsType) {
            $classroom = $this->getClassroomService()->getClassroom($targetId);

            return $classroom['title'];
        }
    }

    /**
     * @return ContractService
     */
    private function getContractService()
    {
        return $this->service('Contract:ContractService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
