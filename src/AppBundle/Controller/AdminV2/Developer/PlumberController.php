<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Plumber\Service\PlumberQueueService;
use Symfony\Component\HttpFoundation\Request;

class PlumberController extends BaseController
{
    public function logsAction(Request $request)
    {
        $conditions = [];
        $count = $this->getLogService()->countQueues($conditions);

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $logs = $this->getLogService()->searchQueues(
            $conditions,
            ['id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/developer/plumber/job-logs.html.twig', [
            'logs' => $logs,
            'paginator' => $paginator,
        ]);
    }

    /**
     * @return PlumberQueueService
     */
    protected function getLogService()
    {
        return $this->createService('Plumber:PlumberQueueService');
    }
}
