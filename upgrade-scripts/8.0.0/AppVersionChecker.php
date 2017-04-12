<?php

use Topxia\Service\Common\ServiceKernel;

class AppVersionChecker extends AbstractMigrate
{
    public function update($page)
    {
    	$apps = $this->getApps();
        $localApps = $this->getAppService()->findApps(0, 1000);
        foreach ($localApps as $key => $localApp) {
            if (!empty($apps[strtolower($localApp['code'])]) && version_compare($localApp['version'], $apps[strtolower($localApp['code'])], '<')) {
                throw new Exception("插件{$localApp['name']}版本太低，请先升级", 1);
            }
        }
    }

    protected function getApps()
    {
        return array(
            'vip'=>'1.6.5',
            'coupon'=>'2.1.5',
            'questionplus'=>'1.2.1',
            'gracefultheme'=>'1.4.23',
            'userimporter'=>'2.1.5',
            'k12main'=>'1.4.8',
            'homework'=>'1.5.5',
            'chargecoin'=>'1.2.5',
            'moneycard'=>'2.0.4',
            'anywhereserver'=>'1.0.4',
            'desire'=>'1.1.6',
            'discount'=>'1.1.7',
            'language'=>'1.0.8',
            'turing'=>'1.1.11',
            'moocmain'=>'1.6.6',
            'userimporter'=>'1.1.1',
            'questionplus'=>'1.1.1',
            'homework'=>'1.2.0',
            'fileshare'=>'1.0.4',
            'coursedatastatistics'=>'1.0.1',
            'groupsell'=>'1.0.2',
            'howzhipopad'=>'1.0.1',
            'exam'=>'1.2.3',
            'lighttheme'=>'2.2.0',
            'crm'=>'1.0.1',
            'favoritereward'=>'1.0.2',
            'rainbowtree'=>'1.0.0',
            'zero'=>'1.0.0',
        );
    }

    protected function getAppService()
    {
        if ($this->isX8()) {
            return ServiceKernel::instance()->createService('CloudPlatform:AppService');
        }
        return ServiceKernel::instance()->createService('CloudPlatform.AppService');
    }
}