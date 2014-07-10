<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ThemeController extends BaseController
{
	 public function pendantAction(Request $request)
    {
    	// $config = $request->query->all();
        
    	$config = (Object)array(
    				'code' =>'recommend-course', 
    				'title' =>'bbbbb',
    				'count' => '2',
    				);
    	$view = $config->code;

        return $this->render("TopxiaWebBundle:Default:{$view}.html.twig",array(
            'config' => $config
        ));
    }
}