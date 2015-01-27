<?php
namespace Topxia\WebBundle\Controller;
use Topxia\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class ClassroomManageController extends BaseController
{   
    public function indexAction($id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        return $this->render("TopxiaWebBundle:ClassroomManage:index.html.twig",array(
            'classroom'=>$classroom));
    }

    public function studentsAction($id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        return $this->render("TopxiaWebBundle:ClassroomManage:students.html.twig",array(
            'classroom'=>$classroom));
    }

    public function teachersAction($id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        return $this->render("TopxiaWebBundle:ClassroomManage:teachers.html.twig",array(
            'classroom'=>$classroom));
    }

    public function setInfoAction($id,Request $request)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        if($request->getMethod()=="POST"){

            $class=$request->request->all();

            $classroom=$this->getClassroomService()->updateClassroom($id,$class);
        }

        return $this->render("TopxiaWebBundle:ClassroomManage:set-info.html.twig",array(
            'classroom'=>$classroom));
    }

    public function setAction($id,Request $request)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        if($request->getMethod()=="POST"){

            $class=$request->request->all();

            $classroom=$this->getClassroomService()->updateClassroom($id,$class);
        }

        return $this->render("TopxiaWebBundle:ClassroomManage:set.html.twig",array(
            'classroom'=>$classroom));
    }


    public function coursesAction($id)
    {   
        $classroom=$this->getClassroomService()->getClassroom($id);

        return $this->render("TopxiaWebBundle:ClassroomManage:courses.html.twig",array(
            'classroom'=>$classroom));
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom.ClassroomService');
    }
}