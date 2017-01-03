<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ChangelogController extends BaseController
{
    public function listAction(Request $request)
    {
        $rootDir = $this->getServiceKernel()->getParameter('kernel.root_dir');
        $logs = file_get_contents($rootDir."/../CHANGELOG");

        return $this->render('TopxiaWebBundle:Changelog:list.html.twig',array(
            'logs' => $logs
        ));
    }
}