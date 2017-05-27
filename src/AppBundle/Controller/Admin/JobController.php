<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use Symfony\Component\HttpFoundation\Request;

class JobController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array();
        $count = $this->getSchedulerService()->countJobs($conditions);

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $jobs = $this->getSchedulerService()->searchJobs(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/jobs/index.html.twig', array(
            'jobs' => $jobs,
            'paginator' => $paginator,
        ));
    }

    public function logsAction(Request $request)
    {
        $conditions = array();
        $count = $this->getSchedulerService()->countJobLogs($conditions);

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $logs = $this->getSchedulerService()->searchJobLogs(
            $conditions,
            array('created_time' => 'DESC', 'id' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/jobs/logs.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
        ));
    }

    public function enabledAction(Request $request, $id)
    {
        $job = $this->getSchedulerService()->enabledJob($id);

        return $this->render('admin/jobs/table-tr.html.twig', array(
            'job' => $job,
        ));
    }

    public function disabledAction(Request $request, $id)
    {
        $job = $this->getSchedulerService()->disabledJob($id);

        return $this->render('admin/jobs/table-tr.html.twig', array(
            'job' => $job,
        ));
    }

    public function firesAction(Request $request, $id)
    {
        $conditions = array(
            'job_id' => $id,
        );
        $count = $this->getSchedulerService()->countJobFires($conditions);

        $paginator = new Paginator(
            $request,
            $count,
            10
        );

        $jobFires = $this->getSchedulerService()->searchJobFires(
            $conditions,
            array('created_time' => 'DESC', 'id' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/jobs/fires.html.twig', array(
            'jobFires' => $jobFires,
            'paginator' => $paginator,
        ));
    }

    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }
}
