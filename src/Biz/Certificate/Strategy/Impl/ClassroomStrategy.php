<?php

namespace Biz\Certificate\Strategy\Impl;

use Biz\Certificate\Strategy\BaseStrategy;
use Biz\Classroom\Service\ClassroomService;

class ClassroomStrategy extends BaseStrategy
{
    public function getTargetModal()
    {
        return 'admin-v2/operating/certificate/target/classroom-modal.html.twig';
    }

    public function count($conditions)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getClassroomService()->countClassrooms($conditions);
    }

    public function search($conditions, $orderBys, $start, $limit)
    {
        $conditions = $this->filterConditions($conditions);

        return $this->getClassroomService()->searchClassrooms($conditions, $orderBys, $start, $limit);
    }

    protected function filterConditions($conditions)
    {
        if (!empty($conditions['keyword'])) {
            $conditions['titleLike'] = $conditions['keyword'];
            unset($conditions['keyword']);
        }

        $conditions['status'] = 'published';

        return $conditions;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
