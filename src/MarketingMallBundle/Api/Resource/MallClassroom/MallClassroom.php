<?php

namespace MarketingMallBundle\Api\Resource\MallClassroom;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Classroom\Service\ClassroomService;

class MallClassroom extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $conditions = $request->query->all();
        $conditions['excludeStatus'] = 'draft';
        $conditions['showable'] = 1;
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $sort = [
            'createdTime' => 'DESC'
        ];
        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            $sort,
            $offset,
            $limit,
            ['id', 'title', 'smallPicture', 'middlePicture', 'price', 'courseNum']
        );
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