<?php
namespace Topxia\WebBundle\Controller;

class BrowserController extends BaseController
{
    public function upgradeAction()
    {
    	return $this->render('TopxiaWebBundle:Browser:upgrade.html.twig');
    }
}