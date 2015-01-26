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
        return $this->forward('TopxiaWebBundle:ClassroomManage:index.html.twig',  array('id' => $id));
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
