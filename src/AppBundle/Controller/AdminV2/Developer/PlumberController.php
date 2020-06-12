<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Common\CommonException;
use Biz\Plumber\Service\PlumberQueueService;
use Biz\Plumber\Service\PlumberService;
use Symfony\Component\HttpFoundation\Request;

class PlumberController extends BaseController
{
    public function indexAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $action = $request->request->get('action');

            if (!in_array($action, ['start', 'restart', 'stop'])) {
                throw $this->createNewException(CommonException::ERROR_PARAMETER());
            }

            $this->getPlumberService()->$action();

            return $this->createJsonResponse(true);
        }

        list($status, $process) = $this->getPlumberService()->getPlumberStatus();

        $infoTemplate = null;
        if (PlumberService::STATUS_EXECUTING == $status) {
            $infoTemplate = $this->renderView('admin-v2/developer/plumber/info.html.twig', [
                'canOperate' => $this->getPlumberService()->canOperate(),
                'status' => $status,
                'process' => $process,
            ]);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->createJsonResponse($infoTemplate);
        }

        list($status, $process) = $this->getPlumberService()->getPlumberStatus();

        return $this->render('admin-v2/developer/plumber/index.html.twig', [
            'canOperate' => $this->getPlumberService()->canOperate(),
            'status' => $status,
            'process' => $process,
        ]);
    }

    public function queueAction(Request $request)
    {
        $conditions = [];
        $count = $this->getQueueService()->countQueues($conditions);

        $paginator = new Paginator(
            $request,
            $count,
            20
        );

        $logs = $this->getQueueService()->searchQueues(
            $conditions,
            ['id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/developer/plumber/queue.html.twig', [
            'logs' => $logs,
            'paginator' => $paginator,
        ]);
    }

    /**
     * @return PlumberService
     */
    protected function getPlumberService()
    {
        return $this->createService('Plumber:PlumberService');
    }

    /**
     * @return PlumberQueueService
     */
    protected function getQueueService()
    {
        return $this->createService('Plumber:PlumberQueueService');
    }
}
