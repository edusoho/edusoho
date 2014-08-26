<?php
namespace Topxia\WebBundle\Controller;
use Topxia\Common\Paginator;

class ClassController extends BaseController
{
    public function showAction()
    {
        return $this->render('TopxiaWebBundle:Class:show.html.twig');
    }

}