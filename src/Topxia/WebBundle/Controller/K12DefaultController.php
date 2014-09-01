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

    public function passwordAction(Request $request){
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }
        if ($request->getMethod() == 'POST') {
            $passwords = $request->request->all();
            $this->getAuthService()->changePassword($user['id'], null, $passwords['newPassword']);
            $this->setFlashMessage('success', '密码修改成功。');
            return $this->redirect($this->generateUrl('homepage'));
        }
        if($user['firstLogin']!=1){
             throw $this->createAccessDeniedException("不是首登陆,请去账号设置处修改密码");
        }
        $this->getUserService()->changeFirstLogin($user['id']);
        return $this->render('TopxiaWebBundle:K12Default:change-password.html.twig',array(
            'user'=>$user
        ));
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
    protected function getAuthService()
    {
        return $this->getServiceKernel()->createService('User.AuthService');
    }
}