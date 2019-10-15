<?php

namespace Tests\Unit;

use AppBundle\System;
use Biz\BaseTestCase;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;
use QiQiuYun\SDK\Service\BaseService;
use Topxia\Service\Common\ServiceKernel;

class DefaultSdkProviderTest extends BaseTestCase
{
    public function testQiQiuYunSdkPlay()
    {
        $biz = ServiceKernel::instance()->getBiz();
        $setting = $biz->service('System:SettingService');
        $setting->set('storage', array('cloud_access_key' => '9TmBc1q6dJPaJbaYK9YT25TZ1kb8HTEQ', 'cloud_secret_key' => 'M0sZ2S5gCVql7AkRJiKMnEMoYXxGRbwE'));
        $setting->set('developer', array(
            'distributor_server' => 'http://test.eduoshotest.com',
            'cloud_api_es_op_server' => 'http://test.eduoshotest.com',
            'mp_service_url' => 'http://test.eduoshotest.com',
            'ai_face_url' => 'http://test.eduoshotest.com',
            'push_url' => 'http://test.eduoshotest.com',
            'cloud_play_server' => 'http://test.eduoshotest.com',
            'cloud_api_notification_server' => 'http://test.eduoshotest.com',
            'cloud_api_wechat_server' => 'http://test.eduoshotest.com',
        ));

        $sdkArray = array(
            'drp',
            'xapi',
            'play',
            'playv2',
            'esOp',
            'mp',
            'aiface',
            'push',
            'notification',
            'wechat',
            'sms',
        );
        foreach ($sdkArray as $value) {
            $this->assertTrue($biz['qiQiuYunSdk.'.$value] instanceof BaseService);
        }
    }

}
