<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Service\CacheService;
use Biz\System\Service\SettingService;
use Biz\System\SettingModule\CourseSetting;
use Biz\Visualization\Job\RefreshActivityLearnDailyJob;
use Biz\Visualization\Job\RefreshCoursePlanLearnDailyJob;
use Biz\Visualization\Job\RefreshCourseTaskResultJob;
use Biz\Visualization\Job\RefreshLearnDailyJob;
use Biz\Visualization\Job\RefreshUserLearnDailyJob;
use Biz\Visualization\Job\UpdateMediaTypeJob;
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

        $default = CourseSetting::defaultVideoMediaSetting;

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
        foreach ($this->getJobCacheNames() as $cache) {
            $cacheName = $this->getCacheService()->get($cache);
            if (!empty($cacheName)) {
                return $this->createJsonResponse(false);
            }
        }

        return $this->createJsonResponse(true);
    }

    protected function getJobCacheNames()
    {
        return [
            RefreshLearnDailyJob::CACHE_NAME,
            RefreshActivityLearnDailyJob::CACHE_NAME,
            RefreshUserLearnDailyJob::CACHE_NAME,
            RefreshCoursePlanLearnDailyJob::CACHE_NAME,
            RefreshCourseTaskResultJob::CACHE_NAME,
            UpdateMediaTypeJob::CACHE_NAME,
        ];
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

        $this->getCacheService()->set(RefreshLearnDailyJob::CACHE_NAME, ['enabled' => 1], time() + 86400);
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

    /**
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }
}
