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
        $user = $this->getCurrentUser();
        return $this->render('TopxiaWebBundle:Class:header-block.html.twig', array(
            'class' => $class,
            'classNav' => $classNav,
            'user' => $user,
            'headTeacher' => $headTeacher,
        ));
    }

    public function statusBlockAction($class)
    {
        $members = $this->getClassesService()->findClassStudentMembers($class['id']);

        $userIds = ArrayToolkit::column($members, 'userId');

        $statuses = $this->getStatusService()->findStatusesByUserIds($userIds, 0, 10);

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