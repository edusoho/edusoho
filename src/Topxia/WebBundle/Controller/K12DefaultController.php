<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class K12DefaultController extends BaseController
{

    public function indexAction ()
    {
        $user = $this->getCurrentUser();
        if ($user->isAdmin()) {
            return $this->redirect($this->generateUrl('admin'));
        }

        if ($user->isTeacher()) {
            return $this->redirect($this->generateUrl('my_teaching'));
        }

        if($user->isParent()){
            $relations=$this->getUserService()->findUserRelationsByFromIdAndType($user['id'],'family');
            $children=$this->getUserService()->findUsersByIds(ArrayToolkit::column($relations, 'toId'));
            $selectedChild=current($children);
            return $this->redirect($this->generateUrl('parent_child_status',array('childId'=>$selectedChild['id'])));
        }

        $class = $this->getClassesService()->getStudentClass($user['id']);
        if (empty($class)) {
            return $this->createMessageResponse('info', '您还没有加入班级，请联系管理员！');
        }

        return $this->redirect($this->generateUrl('class_show', array('classId' => $class['id'])));
    }

    public function loginAction(Request $request)
    {
        $user = $this->getCurrentUser();
        if ($user->isLogin()) {
            return $this->createMessageResponse('info', '你已经登录了', null, 3000, $this->generateUrl('homepage'));
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return $this->render('TopxiaWebBundle:K12Default:login.html.twig',array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            'targetPath' => $this->generateUrl('homepage'),
        ));
    }

    public function passwordAction(Request $request){
        $user = $this->getCurrentUser();
        if (empty($user) || !$user->isLogin()) {
            throw $this->createAccessDeniedException();
        }
        if ($request->getMethod() == 'POST') {
            $passwords = $request->request->all();
            $this->getAuthService()->changePassword($user['id'], null, $passwords['newPassword']);
            $this->getUserService()->changeFirstLogin($user['id']);
            return $this->redirect($this->generateUrl('homepage'));
        }
        if($user['firstLogin']!=1){
             throw $this->createAccessDeniedException("不是首登陆,请去账号设置处修改密码");
        }
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