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

        $this->assertFalse($isAllowed);
    }

    public function testIsAllowedConfigWithEmptyCurrentTheme()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(),
                ),
            )
        );
        $isAllowed = $this->getThemeService()->isAllowedConfig();

        $this->assertFalse($isAllowed);
    }

    public function testIsAllowedConfigWithTrue()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(
                        'code' => 'jianmo',
                        'name' => '简墨',
                        'author' => 'EduSoho官方',
                        'version' => '1.0.0',
                        'supprot_version' => '6.0.0+',
                        'date' => '2015-6-1',
                        'thumb' => 'img/theme.jpg',
                        'uri' => 'jianmo',
                    ),
                ),
            )
        );
        $isAllowed = $this->getThemeService()->isAllowedConfig();

        $this->assertTrue($isAllowed);
    }

    public function testGetThemeConfigByName()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(),
                ),
            )
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
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(),
                ),
            )
        );
        $result = $this->getThemeService()->getCurrentThemeConfig();
        $this->assertEquals('简墨', $result['name']);
    }

    public function testSaveCurrentThemeConfig()
    {
        $this->setThemeSetting();
        $createConfig = $this->createConfig();

        $config = array(
            'color' => 'default',
            'blocks' => array(
                'left' => array(
                    'title' => '',
                    'count' => '6',
                    'categoryId' => '',
                    'orderBy' => 'latest',
                    'code' => 'course-grid-with-condition-index',
                    'defaultTitle' => '课程组件',
                    'id' => 'latestCourse',
                ),
            ),
            'bottom' => '',
        );

        $configUpdate = $this->getThemeService()->saveCurrentThemeConfig($config);

        $this->assertEquals($createConfig['name'], $configUpdate['name']);
        $this->assertEquals($config['color'], $configUpdate['config']['color']);
    }

    public function testSaveCurrentThemeConfigWithEmpty()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(
                        'name' => 'test',
                    ),
                ),
            )
        );
        $config = array(
            'color' => 'default',
            'blocks' => array(
                'left' => array(
                    'title' => '',
                    'count' => '6',
                    'categoryId' => '',
                    'orderBy' => 'latest',
                    'code' => 'course-grid-with-condition-index',
                    'defaultTitle' => '课程组件',
                    'id' => 'latestCourse',
                ),
            ),
            'bottom' => '',
        );

        $configUpdate = $this->getThemeService()->saveCurrentThemeConfig($config);

        $this->assertEquals('test', $configUpdate['name']);
        $this->assertEquals($config['color'], $configUpdate['config']['color']);
    }

    public function testSaveCurrentThemeConfigWithEmptyThemeSettingAndConfig()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(),
                ),
            )
        );
        $config = array(
            'color' => 'default',
            'blocks' => array(
                'left' => array(
                    'title' => '',
                    'count' => '6',
                    'categoryId' => '',
                    'orderBy' => 'latest',
                    'code' => 'course-grid-with-condition-index',
                    'defaultTitle' => '课程组件',
                    'id' => 'latestCourse',
                ),
            ),
            'bottom' => '',
        );
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

        $this->assertEquals(array(), $config['config']);
    }

    public function testResetCurrentConfig()
    {
        $this->setThemeSetting();
        $this->createConfig();

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertEquals(array(), $config['config']);
    }

    public function testResetCurrentConfigWithEmpty()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(),
                ),
            )
        );
        $this->createConfig();

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertEquals(array(), $config['config']);
    }

    public function testResetCurrentConfigWithNotExistThemeSetting()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(
                        'name' => 'test',
                    ),
                ),
            )
        );
        $this->createConfig();

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertEquals(array(), $config['config']);
    }

    public function testResetCurrentConfigWithNotExistThemeAndConfig()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('theme'),
                    'returnValue' => array(
                    ),
                ),
            )
        );

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertEquals(array(), $config['config']);
    }

    public function testChangeThemeWithEmptyTheme()
    {
        $result = $this->getThemeService()->changeTheme(array());
        $this->assertFalse($result);
    }

    public function testChangeThemeWithWrongVersion()
    {
        $themeSetting = $this->setThemeSetting(array('support_version' => '9.0.0+'));
        $result = $this->getThemeService()->changeTheme($themeSetting);
        $this->assertFalse($result);
    }

    public function testChangeTheme()
    {
        $themeSetting = $this->setThemeSetting();
        $result = $this->getThemeService()->changeTheme($themeSetting);
        $this->assertTrue($result);
    }

    protected function setThemeSetting($setting = array())
    {
        $value = array(
            'code' => 'jianmo',
            'name' => '简墨',
            'author' => 'EduSoho官方',
            'version' => '1.0.0',
            'support_version' => '6.0.0+',
            'protocol' => 3,
            'date' => '2015-6-1',
            'thumb' => 'img/theme.jpg',
        );
        $value = array_merge($value, $setting);
        $this->getSettingService()->set('theme', $value);

        return $this->getSettingService()->get('theme', array());
    }

    protected function createConfig()
    {
        $config = array(
            'color' => 'green',
            'blocks' => array(
                'left' => array(
                    'title' => '',
                    'count' => '12',
                    'categoryId' => '',
                    'orderBy' => 'latest',
                    'code' => 'course-grid-with-condition-index',
                    'defaultTitle' => '课程组件',
                    'id' => 'latestCourse',
                ),
            ),
            'bottom' => '',
        );

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
