<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class FileAttechmentController extends BaseController
{
    public function indexAction(Request $request)
    {
        return $this->render('TopxiaAdminBundle:FileAttachment:index.html.twig');
    }
}
