<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/16
 * Time: 15:29
 */

namespace Custom\AdminBundle\Controller;
use Classroom\ClassroomBundle\Controller\ClassroomAdminController as BaseClassroomAdminController;
use Symfony\Component\HttpFoundation\Request;

class ClassroomAdminController extends BaseClassroomAdminController
{
    public function addClassroomAction(Request $request)
    {
        if (!$this->setting('classroom.enabled')) {
            return $this->createMessageResponse('info', '班级功能未开启，请先在 系统-课程设置-班级 中设置开启');
        }

        if ($this->get('security.context')->isGranted('ROLE_ADMIN') !== true) {
            return $this->createMessageResponse('info', '目前只允许管理员创建班级!');
        }

        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '用户未登录，创建班级失败。');
        }

        if ($request->getMethod() == 'POST') {
            $myClassroom = $request->request->all();

            $title = trim($myClassroom['title']);
            if (empty($title)) {
                $this->setFlashMessage('danger', "班级名称不能为空！");

                return $this->render("ClassroomBundle:ClassroomAdmin:classroomadd.html.twig");
            }

            $isClassroomExisted = $this->getClassroomService()->findClassroomByTitle($title);

            if (!empty($isClassroomExisted)) {
                $this->setFlashMessage('danger', "班级名称已被使用，创建班级失败！");

                return $this->render("ClassroomBundle:ClassroomAdmin:classroomadd.html.twig");
            }

            $classroom = array(
                'title' => $myClassroom['title'],
                'showable' => $myClassroom['showable'],
                'buyable' => 1
            );

            $classroom = $this->getClassroomService()->addClassroom($classroom);

            $this->setFlashMessage('success', "恭喜！创建班级成功！");

            return $this->redirect($this->generateUrl('classroom_manage', array('id' => $classroom['id'])));
        }

        return $this->render("ClassroomBundle:ClassroomAdmin:classroomadd.html.twig");
    }
}