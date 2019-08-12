<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use AppBundle\Extensions\DataTag\WeChatUserDataTag;
use Biz\BaseTestCase;

class WeChatUserDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $dataTag = new WeChatUserDataTag();
        $user = $dataTag->getData(array('userId' => 1));
        $this->assertEmpty($user);

        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('wechat_notification_enabled' => 1),
            ),
        ));

        $this->mockBiz('WeChat:WeChatService', array(
            array(
                'functionName' => 'getOfficialWeChatUserByUserId',
                'returnValue' => array('id' => 1),
            ),
        ));

        $user = $dataTag->getData(array('userId' => 1));
        $this->assertEquals(1, $user['id']);
    }
}