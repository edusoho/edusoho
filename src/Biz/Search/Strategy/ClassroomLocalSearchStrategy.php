<?php

namespace Biz\Search\Strategy;

use Biz\Classroom\Service\ClassroomService;

class ClassroomLocalSearchStrategy implements LocalSearchStrategy
{
    use LocalSearchStrategyTrait;

    public function buildSearchConditions($keyword, $filter)
    {
        $this->conditions = [
            'status' => 'published',
            'titleLike' => $keyword,
            'showable' => 1,
        ];

        if ('free' == $filter) {
            $this->conditions['price'] = '0.00';
        }
    }

    public function count()
    {
        return $this->getClassroomService()->countClassrooms($this->conditions);
    }

    public function search($start, $limit)
    {
        return $this->getClassroomService()->searchClassrooms(
            $this->conditions,
            ['updatedTime' => 'desc'],
            $start,
            $limit
        );
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
