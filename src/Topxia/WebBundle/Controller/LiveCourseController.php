<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class LiveCourseController extends BaseController
{
	public function indexAction(Request $request)
	{

	}

  	public function createAction(Request $request)
    {
        if($request->getMethod() == 'POST') {
            $data = $request->query->all();
            var_dump($data);
            exit();
        }
            
        return $this->render('TopxiaWebBundle:LiveCourse:live-lesson-modal.html.twig',array(
        	
        ));
    }
}