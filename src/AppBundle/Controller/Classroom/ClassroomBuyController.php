<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BuyFlowController;
use Biz\Classroom\Service\ClassroomService;

class ClassroomBuyController extends BuyFlowController
{
    protected $targetType = 'classroom';

    protected function tryFreeJoin($id)
    {
        $this->getClassroomService()->tryFreeJoin($id);
    }

    protected function getSuccessUrl($id)
    {
        return $this->generateUrl('classroom_courses', array('classroomId' => $id));
    }

    protected function isJoined($id)
    {
        $user = $this->getUser();

        return $this->getClassroomService()->isClassroomStudent($id, $user['id']);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
