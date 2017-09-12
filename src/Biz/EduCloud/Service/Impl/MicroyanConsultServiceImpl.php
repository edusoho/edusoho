<?php

namespace Biz\EduCloud\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\EduCloud\Service\ConsultService;
use Biz\System\Service\SettingService;

class MicroyanConsultServiceImpl extends BaseService implements ConsultService
{
    public function getAccount()
    {
        $api = CloudAPIFactory::create('root');

        return $api->post('/robot/login_url');
    }

    public function getJsResource()
    {
        $api = CloudAPIFactory::create('root');

        return $api->post('/robot/install');
    }

    public function buildCloudConsult($account, $jsResource)
    {
        $cloudConsult = $this->getSettingService()->get('cloud_consult', array());

        $defaultSetting = array(
            'cloud_consult_setting_enabled' => 0,
            'cloud_consult_is_buy' => 0,
            'cloud_consult_login_url' => '',
            'cloud_consult_js' => '',
        );

        if ((!empty($account['code']) && $account['code'] == '10000') || (!empty($jsResource['code']) && $jsResource['code'] == '10000')) {
            $cloudConsult['cloud_consult_is_buy'] = 0;
        } elseif ((!empty($account['code']) && $account['code'] == '10001') || (!empty($jsResource['code']) && $jsResource['code'] == '10001')) {
            $cloudConsult['cloud_consult_is_buy'] = 0;
            $cloudConsult['error'] = '帐号已过期,请联系客服人员:4008041114！';
        } elseif (!empty($account['error']) || !empty($jsResource['error'])) {
            $cloudConsult['cloud_consult_is_buy'] = 0;
        } else {
            $cloudConsult['cloud_consult_is_buy'] = 1;
            $cloudConsult['cloud_consult_login_url'] = $account['loginUrl'];
            $cloudConsult['cloud_consult_js'] = $jsResource['install'];
        }

        $cloudConsult = array_merge($defaultSetting, $cloudConsult);

        return $cloudConsult;
    }

    /**
     * @return SettingService
     */
    public function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
