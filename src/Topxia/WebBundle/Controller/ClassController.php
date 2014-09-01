<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassController extends ClassBaseController
{
    public function showAction(Request $request, $classId)
    {
        return $this->forward('TopxiaWebBundle:ClassThread:list', array('classId' => $classId), $request->query->all());
    }

    public function headerBlockAction($class, $classNav)
    {
        $headTeacher = $this->getClassesService()->getClassHeadTeacher($class['id']);

        return $this->render('TopxiaWebBundle:Class:header-block.html.twig', array(
            'class' => $class,
            'classNav' => $classNav,
            'headTeacher' => $headTeacher,
        ));
    }

    public function statusBlockAction(Request $request)
    {
        $statuses = $this->getStatusService()->findStatusesByUserIds(array(1,2,3), 0, 10);


        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($statuses, 'userId'));
        
        return $this->render('TopxiaWebBundle:Class:status-block.html.twig', array(
            'statuses' => $statuses,
            'users' => $users,

        ));
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }
}