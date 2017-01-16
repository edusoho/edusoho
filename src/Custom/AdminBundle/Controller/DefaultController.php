<?php

namespace Custom\AdminBundle\Controller;


use AppBundle\Controller\Admin\DefaultController as BaseDefaultController;

class DefaultController extends BaseDefaultController
{
    public function helloAction($name)
    {
        return $this->render('CustomAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
