<?php

namespace Tests\Unit;

use Biz\BaseTestCase;
use Topxia\Service\Common\ServiceKernel;

class DefaultSdkProviderTest extends BaseTestCase
{
    public function testQiQiuYunSdkPlay()
    {
        $biz = ServiceKernel::instance()->getBiz();
        $setting = $biz->service('System:SettingService');
        $setting->set('storage', array('cloud_access_key' => 'test_cloud_access_key', 'cloud_secret_key' => 'test_cloud_secret_key'));
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
            'drp' => '\QiQiuYun\SDK\Service\DrpService',
            'xapi' => '\QiQiuYun\SDK\Service\XAPIService',
            'play' => '\QiQiuYun\SDK\Service\PlayService',
            'playv2' => '\QiQiuYun\SDK\Service\PlayV2Service',
            'esOp' => '\QiQiuYun\SDK\Service\ESopService',
            'mp' => '\QiQiuYun\SDK\Service\MpService',
            'aiface' => '\QiQiuYun\SDK\Service\AiService',
            'push' => '\QiQiuYun\SDK\Service\PushService',
            'notification' => '\QiQiuYun\SDK\Service\NotificationService',
            'wechat' => '\QiQiuYun\SDK\Service\WeChatService',
            'sms' => '\QiQiuYun\SDK\Service\SmsService',
        );
        foreach ($sdkArray as $key => $value) {
            $this->assertTrue($biz['qiQiuYunSdk.'.$key] instanceof $value);
        }
    }
}
