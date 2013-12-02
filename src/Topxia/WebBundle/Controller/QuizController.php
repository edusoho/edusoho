<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class QuizController extends BaseController
{

	public function indexAction(Request $request)
	{
        return $this->render('TopxiaWebBundle:Quiz:quiz-modal.html.twig', array(
			'course' => 1,
		));
	}

    

  

}