<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Service\SettingService;
use Biz\Visualization\Job\RefreshLearnDailyJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\HttpFoundation\Request;

class VideoTaskSettingController extends BaseController
{
    public function taskPlaySettingAction(Request $request)
    {
        $setting = $this->getSettingService()->get('taskPlayMultiple', []);

        $default = [
            'multiple_learn_enable' => '1',
            'multiple_learn_kick_mode' => 'kick_previous',
        ];

        $setting = array_merge($default, $setting);

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $setting = array_merge($setting, $set);

            $this->getSettingService()->set('taskPlayMultiple', $setting);
        }

        return $this->render('admin-v2/system/course-setting/video-task-play-setting.html.twig', [
            'setting' => $setting,
        ]);
    }

    public function videoEffectiveLearningTimeSettingAction(Request $request)
    {
        $effectiveTimeSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);

        $default = [
            'statistical_dimension' => 'page',
            'play_rule' => 'no_action',
        ];

        $effectiveTimeSetting = array_merge($default, $effectiveTimeSetting);

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $this->getSettingService()->set('videoEffectiveTimeStatistics', $set);

            if ($set['statistical_dimension'] != $effectiveTimeSetting['statistical_dimension']) {
                $this->createRefreshDataJob();
            }

            $effectiveTimeSetting = array_merge($effectiveTimeSetting, $set);
        }

        return $this->render('admin-v2/system/course-setting/video-effective-learning-time-setting.html.twig', ['effectiveTimeSetting' => $effectiveTimeSetting]);
    }

    public function refreshJobCheckAction(Request $request)
    {
        $jobName = $this->getSettingService()->get('refreshLearnDailyJob', '');
        if (empty($jobName)) {
            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    protected function createRefreshDataJob()
    {
        $job = [
            'name' => 'RefreshLearnDailyJob',
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => intval(time()),
            'misfire_policy' => 'executing',
            'class' => RefreshLearnDailyJob::class,
            'args' => [],
        ];

        $job = $this->getSchedulerService()->register($job);

        $this->getSettingService()->set('refreshLearnDailyJob', $job['name']);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }
}
