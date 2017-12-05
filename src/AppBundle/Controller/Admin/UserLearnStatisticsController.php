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

    public function syncDailyData()
    {
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
}
