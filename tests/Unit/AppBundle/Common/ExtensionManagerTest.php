<?php

namespace Tests\Unit\AppBundle\Common;

use AppBundle\Common\ExtensionManager;
use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Topxia\Service\Common\ServiceKernel;

class ExtensionManagerTest extends BaseTestCase
{
    public function testInit()
    {
        $manager = ExtensionManager::init(ServiceKernel::instance());
        $result = ReflectionUtils::getProperty($manager, 'bundles');

        $this->assertEquals(4, count($result));
        foreach ($result as $key => $value) {
            $this->assertTrue(in_array($key, ['DataTag', 'StatusTemplate', 'DataDict', 'NotificationTemplate']));
        }
    }

    public function testRenderStatus()
    {
        $instance = ExtensionManager::instance();
        $result = $instance->renderStatus(
            [
                'type' => 'become_student',
                'objectType' => 'course_set',
                'properties' => ['course' => ['id' => 222]],
            ],
            'simple'
        );

        $this->assertEquals('加入学习', trim($result));
    }

    public function testRenderStatusWithNonExistType()
    {
        $instance = ExtensionManager::instance();
        $result = $instance->renderStatus(['type' => 'ok'], 'simple');
        $this->assertEquals('无法显示该动态。', $result);
    }

    public function testGetDataDict()
    {
        $instance = ExtensionManager::instance();
        $result = $instance->getDataDict('nonOpenCourseCateogry');

        $this->assertArrayEquals(
            [
                'live' => 'Living course',
                'normal' => 'Course',
            ],
            $result
        );
    }

    public function testGetDataTag()
    {
        $instance = ExtensionManager::instance();
        $result = $instance->getDataTag('Org');

        $this->assertEquals('AppBundle\Extensions\DataTag\OrgDataTag', get_class($result));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage 数据标签`DolldksOrg`尚未定义。
     */
    public function testGetDataTagWithNonExistName()
    {
        $instance = ExtensionManager::instance();
        $result = $instance->getDataTag('DolldksOrg');
    }

    public function testRenderNotification()
    {
        $instance = ExtensionManager::instance();
        $result = $instance->renderNotification(
            [
                'type' => 'default',
                'content' => [
                    'message' => 'bok',
                ],
                'createdTime' => 1523762123,
            ]
        );
        $expectedHtml = $this->removeBlankAndNewLine(
                '<li class="media">
                <div class="pull-left">
                <span class="glyphicon glyphicon-volume-down media-object"></span>
                </div>
                <div class="media-body">
                <div class="notification-body">
                    bok
                </div>
                <div class="notification-footer">
                          2018-4-15 11:15:23
                </div>
                </div>
            </li>'
            );
        $actualHtml = $this->removeBlankAndNewLine($result);
        $this->assertEquals($expectedHtml, $actualHtml);
    }

    /*
     * 删除空格和换行符
     */
    private function removeBlankAndNewLine($text)
    {
        $text = preg_replace("/\t/", '', $text);
        $text = preg_replace('/ /', '', $text);
        $text = preg_replace("/\n/", '', $text);

        return $text;
    }
}
