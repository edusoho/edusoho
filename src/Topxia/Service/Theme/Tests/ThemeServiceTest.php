<?php

namespace Topxia\Service\Theme\Tests;

use Topxia\Service\Common\BaseTestCase;

class ThemeServiceTest extends BaseTestCase
{
    public function testGetCurrentThemeConfig()
    {
        $this->getThemeService()->getCurrentThemeConfig();
    }

    public function testSaveCurrentThemeConfig()
    {
        $value = '{
        		    "code": "jianmo",
        		    "name": "简墨",
        		    "author": "EduSoho官方",
        		    "version": "1.0.0",
        		    "supprot_version": "6.0.0+",
        		    "date": "2015-6-1",
        		    "thumb": "img/theme.jpg"
        		}';
        $config = array(
            'color'  => 'default',
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
        $this->getSettingService()->set('theme', $value);
        $setting = $this->getSettingService()->get('theme');
        $config  = $this->createThemeConfig('jianmo', $config);
    }

    private function createUser()
    {
        $user              = array();
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;
    }

    protected function getThemes()
    {
        $themes = array();

        $dir    = $this->container->getParameter('kernel.root_dir').'/../web/themes';
        $finder = new Finder();

        foreach ($finder->directories()->in($dir)->depth('== 0') as $directory) {
            $theme = $this->getTheme($directory->getBasename());

            if ($theme) {
                $themes[] = $theme;
            }
        }

        return $themes;
    }

    protected function createThemeConfig($name, $config)
    {
        return $this->getThemeConfigDao()->addThemeConfig(array(
            'name'          => $name,
            'config'        => $config,
            'updatedTime'   => time(),
            'createdTime'   => time(),
            'updatedUserId' => $this->getCurrentUser()->id
        ));
    }

    protected function getThemeService()
    {
        return $this->getServiceKernel()->createService('Theme.ThemeService');
    }

    protected function getThemeConfigDao()
    {
        return $this->getServiceKernel()->createDao('Theme.ThemeConfigDao');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
