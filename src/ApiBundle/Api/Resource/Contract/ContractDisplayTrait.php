<?php

namespace ApiBundle\Api\Resource\Contract;

use Biz\Classroom\Service\ClassroomService;
use Biz\Contract\Service\ContractService;
use Biz\Course\Service\CourseService;

trait ContractDisplayTrait
{
    private function getGoodsName($goodsKey)
    {
        list($goodsType, $targetId) = $this->parseGoodsKey($goodsKey);
        if ('course' == $goodsType) {
            $course = $this->getCourseService()->getCourse($targetId);

            return "{$course['courseSetTitle']}-{$course['title']}";
        }
        if ('classroom' == $goodsType) {
            $classroom = $this->getClassroomService()->getClassroom($targetId);

            return $classroom['title'];
        }
    }

    private function parseGoodsKey($goodsKey)
    {
        return explode('_', $goodsKey);
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
