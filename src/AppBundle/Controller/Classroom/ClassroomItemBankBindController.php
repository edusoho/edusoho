<?php

namespace AppBundle\Controller\Classroom;

use AppBundle\Controller\BaseController;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;

class ClassroomItemBankBindController extends BaseController
{
    public function listAction(Request $request, $classroomId)
    {
        return $this->render('classroom-itemBankBind/list.html.twig', array(
            'classroom' => $this->getClassroomService()->getClassroom($classroomId),
            'member' => [],
        ));
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}