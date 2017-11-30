<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\User\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;

class UserLearnStatisticsController extends BaseController
{
    public function showAction(Request $request)
    {
        $defaultCondition = array(
            'startDate' => '',
            'endDate' => '',
            'nickname' => '',
            'isDefault' => 'false'
        );
        $conditions = $request->query->all();
        list($conditions, $orderBy, $isDefault) = $this->prepareConditions($conditions);

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
        
        $statistics = $this->getLearnStatisticsService()->statisticsDataSearch(
            $conditions,
            $orderBy
        );

        $timespan = $this->getLearnStatisticsService()->getTimespan();
        
        return $this->render('admin/learn-statistics/show.html.twig', array(
            'statistics' => ArrayToolkit::index($statistics, 'userId'),
            'paginator' => $paginator,
            'users' => $users,
            'timespan' => $timespan,
            'isDefault' => $conditions['isDefault']
        ));
    }

    protected function prepareConditions($fields)
    {
        if (!empty($fields['isDefault']) && $fields['isDefault'] == 'true') {
            $orderBy = array('userId' => 'DESC', 'joinedCourseNum' => 'DESC', 'actualAmount' => 'DESC');
            $isDefault = 'true';
            $conditions = array();
        } else {
            $orderBy = array('id' => 'DESC');
            $isDefault = false;
            $conditions = $fields;
        }
        
        return array($conditions, $orderBy, $isDefault);
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