<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ThemeController extends BaseController
{
	 public function pendantAction(Request $request)
    {
    	$config = $request->query->all();
        
        return $this->render('TopxiaWebBundle:Default:xxx.html.twig',array(
            'config' => $config
        ));
    }
}