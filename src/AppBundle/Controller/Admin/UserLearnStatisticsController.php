<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class UserLearnStatisticsController extends BaseController
{
    public function showAction(Request $request)
    {
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getLearnStatisticesService()->countTotalStatistics($conditions),
            20
        );

        $statistics = $this->getLearnStatisticesService()->searchTotalStatistics(
            $conditions,
            array('id' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userIds = ArrayToolkit::column($statistics, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/learn-statistices/show.html.twig', array(
            'statistics' => $statistics,
            'paginator' => $paginator,
            'users' => $users,
        ));
    }

    public function detailAction(Request $request, $userId)
    {
        return $this->render('admin/learn-statistices/detail.html.twig', array(
        ));
    }

    public function syncDailyData()
    {
    }

    protected function getLearnStatisticesService()
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
