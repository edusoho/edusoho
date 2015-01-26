<?php
namespace Topxia\AdminBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassroomController extends BaseController
{

    public function IndexAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'title'=>'',
        );

        if(!empty($fields)){
            $conditions =$fields;
        } 

        $paginator = new Paginator(
            $this->get('request'),
            $this->getClassroomService()->searchClassroomsCount($conditions),
            10
        );

        $classroomInfo=$this->getClassroomService()->searchClassrooms(
                $conditions,
                array('createdTime','desc'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:Classroom:index.html.twig',array(
            'classroomInfo'=>$classroomInfo,
            'paginator' => $paginator));
	
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

                return $this->render("TopxiaAdminBundle:Classroom:classroomadd.html.twig");
            }

            $classroom = array(
                'title' => $myClassroom['title'],
            );

            $classroom = $this->getClassroomService()->addClassroom($classroom);
            return $this->redirect($this->generateUrl('classroom_manage',array('id'=>$classroom['id'])));
        }

        return $this->render("TopxiaAdminBundle:Classroom:classroomadd.html.twig");
    }
    
    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom.ClassroomService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}