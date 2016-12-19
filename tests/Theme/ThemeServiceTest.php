<?php

namespace Tests\Theme;

use Topxia\Service\Common\BaseTestCase;

class ThemeServiceTest extends BaseTestCase
{
    public function testIsAllowedConfig()
    {
        $this->setThemeSetting();

        $isAllowed = $this->getThemeService()->isAllowedConfig();

        $this->assertFalse($isAllowed);
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
        $config       = $this->getThemeService()->getCurrentThemeConfig();

        $this->assertEquals($themeSetting['name'], $config['name']);
    }

    public function testSaveCurrentThemeConfig()
    {
        $this->setThemeSetting();
        $createConfig = $this->createConfig();

        $config = array(
            'color'  => 'default',
            'blocks' => array(
                'left' => array(
                    "title"        => "",
                    "count"        => "6",
                    "categoryId"   => "",
                    "orderBy"      => "latest",
                    "code"         => "course-grid-with-condition-index",
                    "defaultTitle" => "课程组件",
                    "id"           => "latestCourse"
                )
            ),
            'bottom' => ''
        );

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
        $createConfig = $this->createConfig();

        $config = $this->getThemeService()->resetConfig();

        $this->assertArrayEquals(array(), $config['config']);
    }

    public function testResetCurrentConfig()
    {
        $themeSetting = $this->setThemeSetting();
        $themeCreate  = $this->createConfig();

        $config = $this->getThemeService()->resetCurrentConfig();

        $this->assertArrayEquals(array(), $config['config']);
    }

    protected function setThemeSetting()
    {
        $value = array(
            'code'            => 'jianmo',
            'name'            => '简墨',
            'author'          => 'EduSoho官方',
            'version'         => '1.0.0',
            'supprot_version' => '6.0.0+',
            'date'            => '2015-6-1',
            'thumb'           => 'img/theme.jpg'
        );
        $this->getSettingService()->set('theme', $value);

        return $this->getSettingService()->get('theme', array());
    }

    protected function createConfig()
    {
        $config = array(
            'color'  => 'green',
            'blocks' => array(
                'left' => array(
                    "title"        => "",
                    "count"        => "12",
                    "categoryId"   => "",
                    "orderBy"      => "latest",
                    "code"         => "course-grid-with-condition-index",
                    "defaultTitle" => "课程组件",
                    "id"           => "latestCourse"
                )
            ),
            'bottom' => ''
        );

        return $this->getThemeService()->createThemeConfig('简墨', $config);
    }

    protected function getThemeService()
    {
        return $this->getBiz()->service('Theme:ThemeService');
    }

    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
