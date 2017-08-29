<?php 

namespace AppBundle\Controller\Callback;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WeixinController extends BaseController
{
    public function notifyAction(Request $request)
    {
        return new Response('SUCCESS');
    }
}