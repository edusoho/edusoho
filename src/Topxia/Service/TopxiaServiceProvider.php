<?php

namespace Topxia\Service;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Topxia\Service\System\Dao\Impl\SettingDaoImpl;
use Topxia\Service\System\Impl\SettingServiceImpl;

class TopxiaServiceProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['setting_service'] = function ($biz){
            return new SettingServiceImpl($biz);
        };

        $biz['setting_dao'] = $biz->dao(function($biz){
            return new SettingDaoImpl($biz);
        });
    }

}