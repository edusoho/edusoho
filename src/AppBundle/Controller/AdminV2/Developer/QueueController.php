<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Symfony\Component\HttpFoundation\Request;

class QueueController extends BaseController
{
    public function failedJobsAction(Request $request)
    {
        $count = $this->getQueueService()->countFailedJobs(array());

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $jobs = $this->getQueueService()->searchFailedJobs(
            array(),
            array('failed_time' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'admin-v2/developer/queue/failed-logs.html.twig',
            array(
                'jobs' => $jobs,
                'paginator' => $paginator,
            )
        );
    }

    public function failedJobAction(Request $request, $id)
    {
        $failedJob = $this->getQueueService()->getFailedJob($id);

        return $this->render(
            'admin-v2/developer/queue/failed-log-modal.html.twig',
            array(
                'failedJob' => $failedJob,
            )
        );
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->createService('Queue:QueueService');
    }
}
