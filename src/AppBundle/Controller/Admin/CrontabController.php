<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\System;
use Symfony\Component\HttpFoundation\Request;
use TiBeN\CrontabManager\CrontabAdapter;
use TiBeN\CrontabManager\CrontabRepository;

class CrontabController extends BaseController
{
    public function indexAction()
    {
        if (System::getOS() === System::OS_WIN || System::getOS() === System::OS_UNKNOWN) {
            return $this->render('admin/crontab/error.html.twig');
        }

        $crontabRepository = new CrontabRepository(new CrontabAdapter());

        $crontabJobs = $crontabRepository->findJobByRegex('/app\/console util\:scheduler/');

        return $this->render('admin/crontab/index.html.twig', array(
            'crontabJobs' => $crontabJobs
        ));
    }
}