<?php

namespace AppBundle\Controller\AdminV2\Developer;

use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Visualization\Dao\ActivityLearnRecordDao;
use Biz\Visualization\Dao\ActivityStayDailyDao;
use Biz\Visualization\Dao\ActivityVideoWatchRecordDao;
use Biz\Visualization\Dao\UserActivityLearnFlowDao;
use Biz\Visualization\Service\ActivityDataDailyStatisticsService;
use Symfony\Component\HttpFoundation\Request;

class VisualizationController extends BaseController
{
    public function activityLearnRecordAction(Request $request)
    {
        $conditions = $request->query->all();
        unset($conditions['page']);
        $paginator = new Paginator(
            $request,
            $this->getActivityLearnRecordDao()->count($conditions),
            20
        );
        $records = $this->getActivityLearnRecordDao()->search(
            $conditions,
            ['id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/developer/visualization/activity-learn-record.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
        ]);
    }

    public function activityVideoWatchRecordAction(Request $request)
    {
        $conditions = $request->query->all();
        unset($conditions['page']);
        $paginator = new Paginator(
            $request,
            $this->getActivityVideoWatchRecordDao()->count($conditions),
            20
        );
        $records = $this->getActivityVideoWatchRecordDao()->search(
            $conditions,
            ['id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/developer/visualization/activity-video-watch-record.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
        ]);
    }

    public function activityLearnFlowAction(Request $request)
    {
        $conditions = $request->query->all();
        unset($conditions['page']);
        $paginator = new Paginator(
            $request,
            $this->getActivityLearnFlowDao()->count($conditions),
            20
        );
        $records = $this->getActivityLearnFlowDao()->search(
            $conditions,
            ['id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/developer/visualization/activity-learn-flow.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
        ]);
    }

    public function activityStayDailyAction(Request $request)
    {
        $conditions = $request->query->all();
        unset($conditions['page']);
        $paginator = new Paginator(
            $request,
            $this->getActivityStayDailyDao()->count($conditions),
            20
        );

        $records = $this->getActivityStayDailyDao()->search(
            $conditions,
            ['id' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin-v2/developer/visualization/activity-stay-daily.html.twig', [
            'records' => $records,
            'paginator' => $paginator,
        ]);
    }

    public function activityStayDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsPageStayDailyData($startTime, $startTime + 86400);

        return $this->createJsonResponse(true);
    }

    public function activityVideoDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsVideoDailyData($startTime, $startTime + 86400);

        return $this->createJsonResponse(true);
    }

    public function coursePlanStayDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanStayDailyData($startTime, $startTime + 86400);

        return $this->createJsonResponse(true);
    }

    public function coursePlanVideoDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanVideoDailyData($startTime, $startTime + 86400);

        return $this->createJsonResponse(true);
    }

    public function userStayDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsUserStayDailyData($startTime, $startTime + 86400);

        return $this->createJsonResponse(true);
    }

    public function userVideoDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsUserVideoDailyData($startTime, $startTime + 86400);

        return $this->createJsonResponse(true);
    }

    public function userLearnDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsUserLearnDailyData($startTime);

        return $this->createJsonResponse(true);
    }

    public function coursePlanLearnDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsCoursePlanLearnDailyData($startTime);

        return $this->createJsonResponse(true);
    }

    public function activityLearnDailyStatisticsAction(Request $request)
    {
        $day = $request->query->get('date', 'today');
        $startTime = strtotime($day);
        $this->getActivityDataDailyStatisticsService()->statisticsLearnDailyData($startTime);
        $this->getActivityDataDailyStatisticsService()->sumTaskResultTime($startTime);

        return $this->createJsonResponse(true);
    }

    /**
     * @return ActivityStayDailyDao
     */
    protected function getActivityStayDailyDao()
    {
        return $this->getBiz()->dao('Visualization:ActivityStayDailyDao');
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

    /**
     * @return ActivityDataDailyStatisticsService
     */
    protected function getActivityDataDailyStatisticsService()
    {
        return $this->getBiz()->service('Visualization:ActivityDataDailyStatisticsService');
    }
}
