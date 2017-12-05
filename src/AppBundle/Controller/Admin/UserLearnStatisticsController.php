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
        $paginator = new Paginator(
            $request,
            $this->getUserService()->countUsers(array()),
            20
        );
        $users = $this->getUserService()->searchUsers(
            array('nickname' => $conditions['nickname']),
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $conditions = array_merge($conditions, array('userIds' => ArrayToolkit::column($users, 'id')));

        $statistics = $this->getLearnStatisticsService()->statisticsDataSearch($conditions);

        $timespan = $this->getLearnStatisticsService()->getTimespan();

        return $this->render('admin/learn-statistics/show.html.twig', array(
            'statistics' => ArrayToolkit::index($statistics, 'userId'),
            'paginator' => $paginator,
            'users' => $users,
            'timespan' => $timespan,
            'isDefault' => $conditions['isDefault'],
        ));
    }

    public function detailAction(Request $request, $userId)
    {
        $user = $this->getUserService()->getUser($userId);
        $overview = $this->getLearnStatisticsService()->getUserOverview($userId);
        $paginator = new Paginator(
            $request,
            $overview['learningCoursesCount'],
            10
        );
        list($courses, $courseSets, $members) = $this->getLearnStatisticsService()->getLearningCourseDetails(
            $userId,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('admin/learn-statistices/detail.html.twig', array(
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
        $startTime = !empty($fields['startTime']) ? $fields['startTime'] : date('Y-m-d', time() - 7 * 24 * 60 * 60);
        $endTime = !empty($fields['endTime']) ? $fields['endTime'] : date('Y-m-d', time());

        return array(
            'startTime' => strtotime($startTime),
            'endTime' => strtotime($endTime) + 24 * 3600 - 1,
        );
    }

    public function syncDailyData()
    {
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
}
