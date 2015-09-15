<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/15
 * Time: 09:23
 */

namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\DefaultController as BaseDefaultController;

class DefaultController extends BaseDefaultController
{
    public function indexAction()
    {
        return $this->render('TopxiaWebBundle:Default:index.html.twig');
    }

}