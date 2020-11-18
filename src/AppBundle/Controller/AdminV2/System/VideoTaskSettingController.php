<?php

namespace AppBundle\Controller\AdminV2\System;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class VideoTaskSettingController extends BaseController
{
    public function videoTaskPlaySettingAction(Request $request)
    {
        $videoSetting = $this->getSettingService()->get('videoTaskPlay', []);

        $default = [
            'same_video_multiple' => '0',
            'different_video_multiple' => '0',
            'multiple_forbidden_effect' => 'kick_previous',
            'play_rule' => 'auto_pause',
        ];

        $videoSetting = array_merge($default, $videoSetting);

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $videoSetting = array_merge($videoSetting, $set);

            $this->getSettingService()->set('videoTaskPlay', $set);
        }

        return $this->render('admin-v2/system/course-setting/video-task-play-setting.html.twig', [
            'videoSetting' => $videoSetting,
        ]);
    }

    public function videoEffectiveLearningTimeSettingAction(Request $request)
    {
        $effectiveTimeSetting = $this->getSettingService()->get('videoEffectiveTimeStatistics', []);

        $default = [
            'statistical_dimension' => 'playing',
            'play_rule' => 'auto_pause',
        ];

        $effectiveTimeSetting = array_merge($default, $effectiveTimeSetting);

        if ('POST' == $request->getMethod()) {
            $set = $request->request->all();

            $effectiveTimeSetting = array_merge($effectiveTimeSetting, $set);

            $this->getSettingService()->set('videoEffectiveTimeStatistics', $set);
        }

        return $this->render('admin-v2/system/course-setting/video-effective-learning-time-setting.html.twig', ['effectiveTimeSetting' => $effectiveTimeSetting]);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
