<?php

namespace Topxia\WebBundle\Controller;

class DefaultController extends BaseController
{

    public function indexAction ()
    {
        return $this->redirect($this->generateUrl('course_explore'));
        $template = ucfirst($this->setting('site.homepage_template', 'less'));

        return $this->forward("TopxiaWebBundle:Default:index{$template}");
    }

    public function indexLessAction()
    {
        return $this->render('TopxiaWebBundle:Default:index-less.html.twig', array(
        ));
    }

    public function indexMoreAction()
    {
        return $this->render('TopxiaWebBundle:Default:index-more.html.twig', array(
        ));
    }

    public function topNavigationAction()
    {
    	$navigations = $this->getNavigationService()->findNavigationsByType('top', 0, 100);

    	return $this->render('TopxiaWebBundle:Default:top-navigation.html.twig', array(
    		'navigations' => $navigations,
		));
    }

    public function footNavigationAction()
    {
        $navigations = $this->getNavigationService()->findNavigationsByType('foot', 0, 100);

        return $this->render('TopxiaWebBundle:Default:foot-navigation.html.twig', array(
            'navigations' => $navigations,
        ));
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }


}
