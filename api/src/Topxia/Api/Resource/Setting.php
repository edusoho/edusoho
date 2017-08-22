<?php

namespace Topxia\Api\Resource;

use Biz\System\Service\SettingService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class Setting extends BaseResource
{
    public function get(Application $app, Request $request, $settingName)
    {
        if (!$this->verifyPermission($settingName)) {
            return array();
        }

        $method = 'filter'.ucfirst($settingName);
        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method));
        } else {
            return array();
        }
    }

    /**
     * 根据设置名称, 验证当前用户是否可获取该设置信息
     * @param $settingName
     * @return bool
     */
    protected function verifyPermission($settingName)
    {
        if (!$this->canAccess($settingName)) {
            return false;
        }

        $config = $this->getAccessConfig();
        $currentUser = $this->getCurrentUser();

        if (isset($config[$settingName]['needToken']) && $config[$settingName]['needToken'] && !$currentUser->isLogin()
        ) {
            return false;
        }

        return true;
    }

    public function filter($res)
    {
        return $res;
    }


    protected function filterCourse()
    {
        $res = $this->getSettingService()->get('course');

        $default = array(
            'chapter_name' => '章',
            'part_name' => '节',
            'show_student_num_enabled' => '1'
        );
        $res = array_merge($default, $res);

        return ArrayToolkit::filter($res, $default);
    }

    protected function filterApp_im()
    {
        $res = $this->getSettingService()->get('app_im');

        $default = array(
            'enabled' => '0',
            'convNo' => null
        );
        
        $res = array_merge($default, $res);

        return ArrayToolkit::filter($res, $default);
    }

    protected function filterUser()
    {
        $authSetting = $this->getSettingService()->get('auth');
        $loginSetting = $this->getSettingService()->get('login_bind');

        $default = array(
            'register_mode' => $authSetting['register_mode'],
            'oauth_enabled' => (string)$loginSetting['enabled'] ?: '0',
            'weibo_enabled' => $loginSetting['weibo_enabled'] ?: '0',
            'qq_enabled' => $loginSetting['qq_enabled'] ?: '0',
            'renren_enabled' => $loginSetting['renren_enabled'] ?: '0',
            'weixinweb_enabled' => $loginSetting['weixinweb_enabled'] ?: '0',
            'weixinmob_enabled' => $loginSetting['weixinmob_enabled'] ?: '0'
        );

        return $default;
    }

    protected function filterKeys(array $input, array $notAllowed)
    {
        return array_diff_key($input, array_flip($notAllowed));
    }

    protected function canAccess($settingName)
    {
        $config = $this->getAccessConfig();

        return array_key_exists($settingName, $config);
    }

    protected function getAccessConfig()
    {
        return array(
            'course' => array(
                'needToken' => false,
            ),
            'app_im' => array(
                'needToken' => true,
            ),
            'user' => array(
                'needToken' => true,
            )
        );
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
