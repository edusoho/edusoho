<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Biz\Visualization\Dao\ActivityVideoWatchRecordDao;
use Biz\Visualization\Dao\UserActivityLearnFlowDao;
use Symfony\Component\HttpFoundation\Request;

class VisualizationController extends BaseController
{
    public function activityLearnRecordAction(Request $request)
    {
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getActivityLearnRecordDao()->count($conditions),
            20
        );
        $records = $this->getActivityLearnRecordDao()->search($conditions, ['id' => 'DESC'], 0, 20);

        return $this->render('admin-v2/developer/visualization/activity-learn-record.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
        ]);
    }

    public function activityVideoWatchRecordAction(Request $request)
    {
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getActivityVideoWatchRecordDao()->count($conditions),
            20
        );
        $records = $this->getActivityVideoWatchRecordDao()->search($conditions, ['id' => 'DESC'], 0, 20);

        return $this->render('admin-v2/developer/visualization/activity-video-watch-record.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
        ]);
    }

    public function activityLearnFlowAction(Request $request)
    {
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getActivityLearnFlowDao()->count($conditions),
            20
        );
        $records = $this->getActivityLearnFlowDao()->search($conditions, ['id' => 'DESC'], 0, 20);

        return $this->render('admin-v2/developer/visualization/activity-learn-flow.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
        ]);
    }

    public function activityStayDailyAction()
    {
    }

    /**
     * @return ActivityLearnRecordDao
     */
    protected function getActivityLearnRecordDao()
    {
        return $this->getBiz()->dao('Visualization:ActivityLearnRecordDao');
    }

    /**
     * @return ActivityVideoWatchRecordDao
     */
    protected function getActivityVideoWatchRecordDao()
    {
        return $this->getBiz()->dao('Visualization:ActivityVideoWatchRecordDao');
    }

    /**
     * @return UserActivityLearnFlowDao
     */
    protected function getActivityLearnFlowDao()
    {
        return $this->getBiz()->dao('Visualization:UserActivityLearnFlowDao');
    }
}
