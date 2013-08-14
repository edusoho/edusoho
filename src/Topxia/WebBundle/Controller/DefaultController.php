<?php

namespace Topxia\WebBundle\Controller;

class DefaultController extends BaseController
{

    public function indexAction ()
    {
        return $this->redirect($this->generateUrl('course_explore'));
    }

    public function navigationAction()
    {
    	$navigations = $this->getNavigationService()->findNavigationsByType('top', 0, 100);

    	return $this->render('TopxiaWebBundle:Default:navigation.html.twig', array(
    		'navigations' => $navigations,
		));
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }


}
