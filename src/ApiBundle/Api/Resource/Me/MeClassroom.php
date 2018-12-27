<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;
use ApiBundle\Api\Annotation\ResponseFilter;
use AppBundle\Common\ArrayToolkit;

class MeClassroom extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Classroom\ClassroomFilter", mode="simple")
     */
    public function search(ApiRequest $request)
    {
        $querys = $request->query->all();

        $conditions = array(
            'userId' => $this->getCurrentUser()->getId(),
            'roles' => array('student', 'auditor', 'assistant'),
        );

        $total = $this->getClassroomService()->searchMemberCount($conditions);

        if (isset($querys['format']) && 'pagelist' == $querys['format']) {
            list($offset, $limit) = $this->getOffsetAndLimit($request);

            $classrooms = $this->getClassrooms($conditions, array(), $offset, $limit);

            return $this->makePagingObject($classrooms, $total, $offset, $limit);
        } else {
            return $this->getClassrooms($conditions, array(), 0, $total);
        }
    }

    private function getClassrooms($conditions, $orderBy, $offset, $limit)
    {
        $classroomIds = ArrayToolkit::column(
            $this->getClassroomService()->searchMembers($conditions, array(), $offset, $limit),
            'classroomId'
        );

        return array_values($this->getClassroomService()->findClassroomsByIds($classroomIds));
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }
}
