<?php

namespace AppBundle\Controller\Admin;

use AppBundle\System;
use Biz\Crontab\SystemCrontabInitializer;

class CrontabController extends BaseController
{
    public function indexAction()
    {
        if (System::getOS() === System::OS_WIN || System::getOS() === System::OS_UNKNOWN) {
            return $this->render('admin/crontab/error.html.twig');
        }

        $crontabJobs = SystemCrontabInitializer::findCrontabJobs();

        return $this->render('admin/crontab/index.html.twig', array(
            'crontabJobs' => $crontabJobs,
        ));
    }

    public function restoreAction()
    {
        SystemCrontabInitializer::init();

        return $this->createJsonResponse(array('success' => 1));
    }
}
