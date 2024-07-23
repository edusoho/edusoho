<?php

namespace Tests\Unit\Theme\Service;

use Biz\BaseTestCase;
use Biz\Theme\Service\ThemeService;

class ThemeServiceTest extends BaseTestCase
{
    public function testIsAllowedConfig()
    {
        $this->setThemeSetting();

        $isAllowed = $this->getThemeService()->isAllowedConfig();

        $this->assertTrue($isAllowed);
    }

    public function testIsAllowedConfigWithEmptyCurrentTheme()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [],
                ],
            ]
        );
        $isAllowed = $this->getThemeService()->isAllowedConfig();

        $this->assertTrue($isAllowed);
    }

    public function testIsAllowedConfigWithTrue()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [
                        'code' => 'jianmo',
                        'name' => '简墨',
                        'author' => 'EduSoho官方',
                        'version' => '1.0.0',
                        'supprot_version' => '6.0.0+',
                        'date' => '2015-6-1',
                        'thumb' => 'img/theme.jpg',
                        'uri' => 'jianmo',
                    ],
                ],
            ]
        );
        $isAllowed = $this->getThemeService()->isAllowedConfig();

        $this->assertTrue($isAllowed);
    }

    public function testGetThemeConfigByName()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [],
                ],
            ]
        );
        $result = $this->getThemeService()->getThemeConfigByName('简墨');
        $this->assertEquals('简墨', $result['name']);
    }

    public function testCreateThemeConfig()
    {
        $config = $this->createConfig();

        $theme = $this->getThemeService()->getThemeConfigByName($config['name']);

        $this->assertEquals($config['name'], $theme['name']);
    }

    public function testGetCurrentThemeConfig()
    {
        $themeSetting = $this->setThemeSetting();
        $config = $this->getThemeService()->getCurrentThemeConfig();

        $this->assertEquals($themeSetting['name'], $config['name']);
    }

    public function testGetCurrentThemeConfigWithEmpty()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [],
                ],
            ]
        );
        $result = $this->getThemeService()->getCurrentThemeConfig();
        $this->assertEquals('简墨', $result['name']);
    }

    public function testSaveCurrentThemeConfig()
    {
        $this->setThemeSetting();
        $createConfig = $this->createConfig();

        $config = [
            'color' => 'default',
            'blocks' => [
                'left' => [
                    'title' => '',
                    'count' => '6',
                    'categoryId' => '',
                    'orderBy' => 'latest',
                    'code' => 'course-grid-with-condition-index',
                    'defaultTitle' => '课程组件',
                    'id' => 'latestCourse',
                ],
            ],
            'bottom' => '',
        ];

        $configUpdate = $this->getThemeService()->saveCurrentThemeConfig($config);

        $this->assertEquals($createConfig['name'], $configUpdate['name']);
        $this->assertEquals($config['color'], $configUpdate['config']['color']);
    }

    public function testSaveCurrentThemeConfigWithEmpty()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [
                        'name' => 'test',
                    ],
                ],
            ]
        );
        $config = [
            'color' => 'default',
            'blocks' => [
                'left' => [
                    'title' => '',
                    'count' => '6',
                    'categoryId' => '',
                    'orderBy' => 'latest',
                    'code' => 'course-grid-with-condition-index',
                    'defaultTitle' => '课程组件',
                    'id' => 'latestCourse',
                ],
            ],
            'bottom' => '',
        ];

        $configUpdate = $this->getThemeService()->saveCurrentThemeConfig($config);

        $this->assertEquals('test', $configUpdate['name']);
        $this->assertEquals($config['color'], $configUpdate['config']['color']);
    }

    public function testSaveCurrentThemeConfigWithEmptyThemeSettingAndConfig()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [],
                ],
            ]
        );
        $config = [
            'color' => 'default',
            'blocks' => [
                'left' => [
                    'title' => '',
                    'count' => '6',
                    'categoryId' => '',
                    'orderBy' => 'latest',
                    'code' => 'course-grid-with-condition-index',
                    'defaultTitle' => '课程组件',
                    'id' => 'latestCourse',
                ],
            ],
            'bottom' => '',
        ];
        $createConfig = $this->createConfig();

        $configUpdate = $this->getThemeService()->saveCurrentThemeConfig($config);

        $this->assertEquals($createConfig['name'], $configUpdate['name']);
        $this->assertEquals($config['color'], $configUpdate['config']['color']);
    }

    public function testSaveConfirmConfig()
    {
        $this->setThemeSetting();
        $createConfig = $this->createConfig();

        $config = $this->getThemeService()->saveConfirmConfig();

        $this->assertEquals($createConfig['config']['color'], $config['confirmConfig']['color']);
    }

    public function testResetConfig()
    {
        $this->setThemeSetting();
        $this->createConfig();

        $config = $this->getThemeService()->resetConfig();

        $this->assertEquals('default', $config['config']['maincolor']);
        $this->assertEquals('default', $config['config']['navigationcolor']);
        $this->assertEquals(9, count($config['config']['blocks']['left']));
        $this->assertEquals('simple', $config['config']['bottom']);
    }

    public function testResetCurrentConfig()
    {
        $this->setThemeSetting();
        $this->createConfig();

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertEquals('default', $config['config']['maincolor']);
        $this->assertEquals('default', $config['config']['navigationcolor']);
        $this->assertEquals(9, count($config['config']['blocks']['left']));
        $this->assertEquals('simple', $config['config']['bottom']);
    }

    public function testResetCurrentConfigWithEmpty()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [],
                ],
            ]
        );
        $this->createConfig();

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertEquals('default', $config['config']['maincolor']);
        $this->assertEquals('default', $config['config']['navigationcolor']);
        $this->assertEquals(9, count($config['config']['blocks']['left']));
        $this->assertEquals('simple', $config['config']['bottom']);
    }

    public function testResetCurrentConfigWithNotExistThemeSetting()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [
                        'name' => 'test',
                    ],
                ],
            ]
        );
        $this->createConfig();

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertEquals('default', $config['config']['maincolor']);
        $this->assertEquals('default', $config['config']['navigationcolor']);
        $this->assertEquals(9, count($config['config']['blocks']['left']));
        $this->assertEquals('simple', $config['config']['bottom']);
    }

    public function testResetCurrentConfigWithNotExistThemeAndConfig()
    {
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'withParams' => ['theme'],
                    'returnValue' => [
                    ],
                ],
            ]
        );

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertEquals('default', $config['config']['maincolor']);
        $this->assertEquals('default', $config['config']['navigationcolor']);
        $this->assertEquals(9, count($config['config']['blocks']['left']));
        $this->assertEquals('simple', $config['config']['bottom']);
    }

    public function testChangeThemeWithEmptyTheme()
    {
        $result = $this->getThemeService()->changeTheme([]);
        $this->assertFalse($result);
    }

    public function testChangeThemeWithWrongVersion()
    {
        $themeSetting = $this->setThemeSetting(['support_version' => '30.0.0+']);
        $result = $this->getThemeService()->changeTheme($themeSetting);
        $this->assertFalse($result);
    }

    public function testChangeTheme()
    {
        $themeSetting = $this->setThemeSetting();
        $result = $this->getThemeService()->changeTheme($themeSetting);
        $this->assertTrue($result);
    }

    protected function setThemeSetting($setting = [])
    {
        $value = [
            'code' => 'jianmo',
            'name' => '简墨',
            'author' => 'EduSoho官方',
            'version' => '1.0.0',
            'support_version' => '6.0.0+',
            'protocol' => 3,
            'date' => '2015-6-1',
            'thumb' => 'img/theme.jpg',
        ];
        $value = array_merge($value, $setting);
        $this->getSettingService()->set('theme', $value);

        return $this->getSettingService()->get('theme', []);
    }

    protected function createConfig()
    {
        $config = [
            'color' => 'green',
            'blocks' => [
                'left' => [
                    'title' => '',
                    'count' => '12',
                    'categoryId' => '',
                    'orderBy' => 'latest',
                    'code' => 'course-grid-with-condition-index',
                    'defaultTitle' => '课程组件',
                    'id' => 'latestCourse',
                ],
            ],
            'bottom' => '',
        ];

        return $this->getThemeService()->createThemeConfig('简墨', $config);
    }

    /**
     * @return ThemeService
     */
    protected function getThemeService()
    {
        return $this->createService('Theme:ThemeService');
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
