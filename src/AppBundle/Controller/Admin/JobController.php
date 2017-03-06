<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class JobController extends BaseController
{
    public function indexAction(Request $request)
    {
        $fields = $request->query->all();
        $fields = ArrayToolkit::filter($fields, array(
            'nextExcutedStartTime' => '',
            'nextExcutedEndTime' => '',
            'name' => '',
            'cycle' => '',
        ));
        $paginator = new Paginator(
            $this->get('request'),
            $this->getJobService()->searchJobsCount($fields),
            30
        );

        $jobs = $this->getJobService()->searchJobs(
            $fields,
            'nextExcutedTime',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/system/jobs.html.twig', array(
            'jobs' => $jobs,
            'paginator' => $paginator,
        ));
    }

    protected function getJobService()
    {
        return $this->createService('Crontab:CrontabService');
    }
}
