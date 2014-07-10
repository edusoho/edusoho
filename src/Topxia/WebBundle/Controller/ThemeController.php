<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ThemeController extends BaseController
{
	public function pendantAction($config=null)
    {
        if (isset($config['code'])) {
            return $this->render("TopxiaWebBundle:Default:{$config['code']}.html.twig",array(
                'config' => $config
            ));
        } 
        if (isset($config['bottom'])) {
            $config = $config['bottom'];
            return $this->render("TopxiaWebBundle:Default:{$config}-bottom.html.twig"); 
        }
    }
}