<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MobileController extends BaseController
{
    public function indexAction(Request $request)
    {

        return $this->render('TopxiaWebBundle:Mobile:index.html.twig', array(
            'host' => $request->getHttpHost(),

        ));
    }

}