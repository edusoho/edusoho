<?php

namespace Topxia\WebBundle\Controller;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ClassroomController extends BaseController 
{
        public function indexAction(Request $request, $id)
    {
        return $this->forward('TopxiaWebBundle:CourseManage:base',  array('id' => $id));
    }

    public function addClassroomAction(Request $request) 
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')!==true) {
            return $this->createMessageResponse('info', '目前只允许管理员创建班级!');
        }

        $user = $this->getCurrentUser();
  
        if (!$user->isLogin()) {
            return $this->createErrorResponse($request, 'not_login', '用户未登录，创建班级失败。');
        }

        if ($request->getMethod() == 'POST') {

            $myClassroom = $request->request->all();

            $title=trim($myClassroom['title']);
            if(empty($title)){
                $this->setFlashMessage('danger',"班级名称不能为空！");

                return $this->render("TopxiaWebBundle:Classroom:classroomadd.html.twig");
            }

            $classroom = array(
                'title' => $myClassroom['title'],
            );

            $classroom = $this->getClassroomService()->addClassroom($classroom);
            return $this->redirect($this->generateUrl('classroom_manage',array('id'=>$classroom['id'])));
        }

        return $this->render("TopxiaWebBundle:Classroom:classroomadd.html.twig");
    }

    

    private function getClassroomService() 
    {
        return $this->getServiceKernel()->createService('Classroom.ClassroomService');
    }



    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
  
}
