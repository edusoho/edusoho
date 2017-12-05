<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
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

        return $this->render('admin/learn-statistics/sync-info.html.twig', array(
            'data' => $data,
        ));
    }

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
