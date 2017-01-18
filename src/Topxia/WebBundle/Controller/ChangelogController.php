<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ChangelogController extends BaseController
{
    public function listAction(Request $request)
    {
        $rootDir = $this->getServiceKernel()->getParameter('kernel.root_dir');
        $changelogUrl = $rootDir."/../CHANGELOG";
        $changelogFile = fopen("{$changelogUrl}", "r");

        $changelogRows = array();
        while(!feof($changelogFile)) {
            $changelogRows[] = fgets($changelogFile);
        }

        fclose($changelogFile);

        return $this->render('TopxiaWebBundle:Changelog:list.html.twig',array(
            'logs' => $changelogRows
        ));
    }
}