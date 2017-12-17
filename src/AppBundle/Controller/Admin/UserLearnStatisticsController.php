<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\DateToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\UserLearnStatistics\Service\LearnStatisticsService;
use Symfony\Component\HttpFoundation\Request;

class UserLearnStatisticsController extends BaseController
{
    public function showAction(Request $request)
    {
        $defaultCondition = array(
            'startDate' => '',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false',
        );
        $conditions = $request->query->all();
        $conditions = array_merge($defaultCondition, $conditions);
        $userConditions = array('nickname' => $conditions['nickname']);
        $paginator = new Paginator(
            $request,
            $this->getUserService()->countUsers($userConditions),
            20
        );
        $users = $this->getUserService()->searchUsers(
            $userConditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $conditions = array_merge($conditions, array('userIds' => ArrayToolkit::column($users, 'id')));

        $statistics = $this->getLearnStatisticsService()->statisticsDataSearch($conditions);

        $recordEndTime = $this->getLearnStatisticsService()->getRecordEndTime();

        return $this->render('admin/learn-statistics/show.html.twig', array(
            'statistics' => ArrayToolkit::index($statistics, 'userId'),
            'paginator' => $paginator,
            'users' => $users,
            'recordEndTime' => $recordEndTime,
            'isDefault' => $conditions['isDefault'],
            'isInit' => $this->getInitStatus(),
        ));
    }

    public function detailAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $overview = $this->getLearnStatisticsService()->getUserOverview($userId);
        $paginator = new Paginator(
            $request,
            empty($overview['learningCoursesCount']) ? 0 : $overview['learningCoursesCount'],
            10
        );
        list($courses, $courseSets, $members) = $this->getLearnStatisticsService()->findLearningCourseDetails(
            $userId,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/learn-statistics/detail.html.twig', array(
            'overview' => $overview,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'paginator' => $paginator,
            'members' => $members,
            'user' => $user,
        ));
    }

    public function learnChartAction(Request $request, $userId)
    {
        $conditions = $request->query->all();
        $timeRange = $this->getTimeRange($conditions);
        $learnData = $this->getLearnStatisticsService()->getDailyLearnData($userId, $timeRange['startTime'], $timeRange['endTime']);
        $learnData = $this->fillAnalysisData($timeRange, $learnData);

        return $this->createJsonResponse($learnData);
    }

    protected function fillAnalysisData($timeRange, $currentData)
    {
        $dateRange = DateToolkit::generateDateRange(
            date('Y-m-d', $timeRange['startTime']),
            date('Y-m-d', $timeRange['endTime'])
        );

        foreach ($dateRange as $key => $value) {
            $zeroData[] = array('date' => $value, 'learnedTime' => 0);
        }

        $currentData = ArrayToolkit::index($currentData, 'date');

        $zeroData = ArrayToolkit::index($zeroData, 'date');

        $currentData = array_merge($zeroData, $currentData);

        $currentData = array_values($currentData);

        return $currentData;
    }

    protected function getTimeRange($fields)
    {
        $startTime = !empty($fields['startTime']) ? $fields['startTime'] : date('Y-m-d', time() - 29 * 24 * 60 * 60);
        $endTime = !empty($fields['endTime']) ? $fields['endTime'] : date('Y-m-d', time());

        return array(
            'startTime' => strtotime($startTime),
            'endTime' => strtotime($endTime) + 24 * 3600 - 1,
        );
    }

    private function getInitStatus()
    {
        $totalJob = $this->getSchedulerService()->searchJobs(array('name' => 'SyncUserTotalLearnStatisticsJob'), array(), 0, 1);
        if (empty($totalJob)) {
            return false;
        }
        $totalJob = reset($totalJob);

        $pastDailyJob = $this->getSchedulerService()->searchJobs(array('name' => 'SyncUserLearnDailyPastLearnStatisticsJob'), array(), 0, 1);
        if (empty($pastDailyJob)) {
            return false;
        }
        $pastDailyJob = reset($pastDailyJob);

        return (0 == $totalJob['enabled'] && 0 == $pastDailyJob['enabled']) ? true : false;
    }

    public function syncInfoAction(Request $request)
    {
        $setting = $this->getLearnStatisticsService()->getStatisticsSetting();
        $data['setting'] = $setting;
        $totalJob = $this->getSchedulerService()->searchJobs(array('name' => 'SyncUserTotalLearnStatisticsJob'), array(), 0, 1);
        $data['totalJob'] = reset($totalJob);

        $pastDailyJob = $this->getSchedulerService()->searchJobs(array('name' => 'SyncUserLearnDailyPastLearnStatisticsJob'), array(), 0, 1);
        $data['pastDailyJob'] = reset($pastDailyJob);

        $dailyJob = $this->getSchedulerService()->searchJobs(array('name' => 'SyncUserLearnDailyLearnStatisticsJob'), array(), 0, 1);
        $data['dailyJob'] = reset($dailyJob);

        $data['dailyNotStorageDataNum'] = $this->getLearnStatisticsService()->countDailyStatistics(array('isStorage' => 0));
        $data['dailyDataNum'] = $this->getLearnStatisticsService()->countDailyStatistics(array());

        return $this->render('admin/learn-statistics/sync-info.html.twig', array(
            'data' => $data,
        ));
    }

    /**
     * @return LearnStatisticsService
     */
    protected function getLearnStatisticsService()
    {
        return $this->createService('UserLearnStatistics:LearnStatisticsService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }
}
