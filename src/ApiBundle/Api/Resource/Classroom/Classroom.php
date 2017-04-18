<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ApiConf;

class Classroom extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $classroomId)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);

        if (empty($classroom)) {
            throw new ResourceNotFoundException('班级不存在');
        }

        $this->getOCUtil()->single($classroom, array('creator', 'teacherIds', 'assistantIds', 'headTeacherId'));

        return $classroom;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['status'] = 'published';

        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            $this->getSort($request),
            $offset,
            $limit
        );

        $this->getOCUtil()->multiple($classrooms, array('creator', 'teacherIds', 'headTeacherId', 'assistantIds'));

        $total = $this->getClassroomService()->countClassrooms($conditions);

        return $this->makePagingObject($classrooms, $total, $offset, $limit);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}