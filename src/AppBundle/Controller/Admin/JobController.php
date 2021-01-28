<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use Biz\SchedulerFacade\Service\SchedulerFacadeService;
use Codeages\Biz\Framework\Scheduler\Service\JobPool;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\HttpFoundation\Request;

class JobController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();
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

        $this->checkPoolHealth();

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
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/jobs/logs.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
        ));
    }

    private function checkPoolHealth()
    {
        $jobPool = new JobPool($this->get('biz'));
        $defPool = $jobPool->getJobPool('default');
        $dedPool = $jobPool->getJobPool('dedicated');

        if (($defPool && $defPool['num'] >= $defPool['max_num'])
            || ($dedPool && $dedPool['num'] >= $dedPool['max_num'])) {
            $this->setFlashMessage('danger', 'There some pool is full, please go to restore.');
        }
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

    public function setNextExecTimeAction(Request $request, $id)
    {
        $job = $this->getSchedulerFacadeService()->getJob($id);
        if ('POST' == $request->getMethod()) {
            $nextFiredTime = $request->request->get('nextExecTime', '');
            if (empty($nextFiredTime)) {
                return $this->createJsonResponse(false);
            }
            $nextFiredTime = strtotime($nextFiredTime);
            $job = $this->getSchedulerFacadeService()->setNextFiredTime($id, $nextFiredTime);
            if (empty($job)) {
                return $this->createJsonResponse(false);
            }

            return $this->createJsonResponse(true);
        }

        return $this->render('admin/jobs/set-next-fired-time-modal.html.twig', array(
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

        $jobFireds = $this->getSchedulerService()->searchJobFires(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/jobs/job-fireds-modal.html.twig', array(
            'jobFireds' => $jobFireds,
            'paginator' => $paginator,
        ));
    }

    public function fireLogsAction(Request $request, $id, $jobFiredId)
    {
        $conditions = array(
            'job_id' => $id,
            'job_fired_id' => $jobFiredId,
        );
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

        return $this->render('admin/jobs/job-fired-logs-modal.html.twig', array(
            'logs' => $logs,
            'paginator' => $paginator,
        ));
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->getBiz()->service('Scheduler:SchedulerService');
    }

    /**
     * @return SchedulerFacadeService
     */
    protected function getSchedulerFacadeService()
    {
        return $this->getBiz()->service('SchedulerFacade:SchedulerFacadeService');
    }
}
