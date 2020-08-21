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

    public function getTarget($targetId)
    {
        return $this->getClassroomService()->getClassroom($targetId);
    }

    public function findTargetsByIds($targetIds)
    {
        return $this->getClassroomService()->findClassroomsByIds($targetIds);
    }

    public function findTargetsByTargetTitle($targetTitle)
    {
        $count = $this->getClassroomService()->countClassrooms(['titleLike' => $targetTitle]);

        return $this->getClassroomService()->searchClassrooms(
            ['titleLike' => $targetTitle],
            [],
            0,
            $count
        );
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

    protected function getContent($record, $content)
    {
        $content = $this->getRecipientContent($record['userId'], $content);

        if (strstr($content, '$classroomName$')) {
            $classroom = $this->getClassroomService()->getClassroom($record['targetId']);
            $content = str_replace('$classroomName$', $classroom['title'], $content);
        }

        return $content;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
