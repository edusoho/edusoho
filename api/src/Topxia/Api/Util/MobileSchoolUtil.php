<?php

namespace Topxia\Api\Util;
use Topxia\Service\Common\ServiceKernel;

class MobileSchoolUtil
{

    private function getSchoolApps()
    {
        $mobile = ServiceKernel::instance()->createService('System.SettingService')->get('mobile');
        $site = ServiceKernel::instance()->createService('System.SettingService')->get('site');
        $apps[1] = array(
            'id' => "1",
            'code' => 'announcement',
            'name' => $site['name'],
            'title' => $site['slogan'],
            'about' => $mobile['about'],
            'avatar' => $mobile['logo'],
            'callback' => '/mobileschools/announcements'
        );

        $apps[2] = array(
            'id' => "2",
            'code' => 'news',
            'name' => '资讯',
            'title' => '网校资讯服务',
            'about' => '',
            'avatar' => 'img/mobile/article_app_icon.jpg',
            'callback' => '',
        );

        return $apps;
    }

    public function searchSchoolApps($conditions = array())
    {
        return array_values($this->getSchoolApps());
    }

    public function findSchoolAppById($id)
    {
        $apps = $this->getSchoolApps();
        return isset($apps[$id]) ? $apps[$id] : array();
    }

    public function getArticleApp()
    {
        $apps = $this->getSchoolApps();
        return $apps[2];
    }

    public function getAnnouncementApp()
    {
        $apps = $this->getSchoolApps();
        return $apps[1];
    }
}