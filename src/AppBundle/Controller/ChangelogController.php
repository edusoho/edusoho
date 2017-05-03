<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class ChangelogController extends BaseController
{
    public function listAction(Request $request)
    {
        $rootDir = $this->getParameter('kernel.root_dir');
        $changelogUrl = $rootDir.'/../CHANGELOG';
        $changelogFile = fopen("{$changelogUrl}", 'r');

        $changelogRows = array();
        while (!feof($changelogFile)) {
            $changelogRows[] = fgets($changelogFile);
        }

        fclose($changelogFile);

        return $this->render('change-log/list.html.twig', array(
            'logs' => $changelogRows,
        ));
    }
}
