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

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom.ClassroomService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}