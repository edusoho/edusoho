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
        $learnSetting = $this->getLearnStatisticesService()->getStatisticsSetting();
        var_dump($learnSetting);   
        exit;
        return $this->render('admin/learn-statistices/show.html.twig');
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
}