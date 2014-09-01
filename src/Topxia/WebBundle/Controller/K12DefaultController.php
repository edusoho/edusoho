<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class K12DefaultController extends BaseController
{

    public function indexAction ()
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return $this->redirect($this->generateUrl('admin'));
        }

        if ($user->isTeacher()) {
            return $this->redirect($this->generateUrl('my_teaching_course'));
        }

        $class = $this->getClassesService()->getStudentClass($user['id']);
        if (empty($class)) {
            return $this->createMessageResponse('info', '您还没有加入班级，请联系管理员！');
        }

        return $this->redirect($this->generateUrl('class_show', array('classId' => $class['id'])));
    }

    public function loginAction()
    {
        
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
}