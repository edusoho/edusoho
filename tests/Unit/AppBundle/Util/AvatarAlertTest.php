<?php

namespace Tests\Unit\AppBundle\Util;

use Biz\BaseTestCase;
use AppBundle\Util\AvatarAlert;

class AvatarAlertTest extends BaseTestCase
{
    public function testAlertJoinCourseWithoutAvatar()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('user_partner'),
                    'returnValue' => array(),
                ),
            )
        );

        $alert = new AvatarAlert();
        $this->assertFalse($alert->alertJoinCourse(array()));

        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testAlertJoinCourseWithOpenAvatarAndUserAvatar()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('user_partner'),
                    'returnValue' => array(
                        'avatar_alert' => 'open',
                    ),
                ),
            )
        );

        $alert = new AvatarAlert();
        $this->assertFalse($alert->alertJoinCourse(array('mediumAvatar' => 'ads.png')));

        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testAlertJoinCourseWithOpenAvatar()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('user_partner'),
                    'returnValue' => array(
                        'avatar_alert' => 'open',
                    ),
                ),
            )
        );

        $alert = new AvatarAlert();
        $this->assertTrue($alert->alertJoinCourse(array('mediumAvatar' => '')));

        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testAlertInMyCenterWithoutAvatar()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('user_partner'),
                    'returnValue' => array(
                        'avatar_alert' => '',
                    ),
                ),
            )
        );

        $alert = new AvatarAlert();
        $this->assertFalse($alert->alertInMyCenter(array('mediumAvatar' => '')));
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testAlertInMyCenterWithAvatarAndUserMediumAvatar()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('user_partner'),
                    'returnValue' => array(
                        'avatar_alert' => 'open',
                    ),
                ),
            )
        );

        $alert = new AvatarAlert();
        $this->assertTrue($alert->alertInMyCenter(array('mediumAvatar' => '')));
        $settingService->shouldHaveReceived('get')->times(1);
    }

    public function testAlertInMyCenterWithOpenAvatarAndUserMediumAvatar()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('user_partner'),
                    'returnValue' => array(
                        'avatar_alert' => 'open',
                    ),
                ),
            )
        );

        $alert = new AvatarAlert();
        $this->assertFalse($alert->alertInMyCenter(array('mediumAvatar' => 'dds.png')));
        $settingService->shouldHaveReceived('get')->times(1);
    }
}
