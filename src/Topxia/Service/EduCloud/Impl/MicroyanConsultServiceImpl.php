<?php

namespace Topxia\Service\EduCloud\Impl;

use Topxia\Service\CloudPlatform\CloudAPIFactory;
use Topxia\Service\EduCloud\ConsultService;
use Topxia\Service\Common\BaseService;

class MicroyanConsultServiceImpl extends BaseService implements ConsultService
{
    public function getAccount()
    {
        $api = CloudAPIFactory::create('root');
        $account = $api->post("/robot/login_url");

        return $account;
    }

    public function getJsResource()
    {
        $api = CloudAPIFactory::create('root');
        $jsResource  = $api->post("/robot/install");

        return $jsResource;
    }

    public function buildCloudConsult($account, $jsResource)
    {
        $cloudConsult = $this->getSettingService()->get('cloud_consult', array());

        $defaultSetting = array(
            'cloud_consult_setting_enabled' => 0,
            'cloud_consult_is_buy' => 0,
            'cloud_consult_login_url' => '',
            'cloud_consult_js' => ''
        );
        if(!is_array($account)){
            $account = array('login_url'=>$account);
        }
        if(!is_array($jsResource)){
            $jsResource = array('js_resource'=>$jsResource);
        }

        if ((!empty($account['code']) && $account['code']== '10000') || (!empty($jsResource['code']) && $jsResource['code']== '10000')) {
            $cloudConsult['cloud_consult_is_buy'] = 0;
        } else if ((!empty($account['code']) && $account['code']== '10001') || (!empty($jsResource['code']) && $jsResource['code']== '10001')) {
            $cloudConsult['cloud_consult_is_buy'] = 0;
            $cloudConsult['error'] = '账号已过期,请联系客服人员:4008041114！';
        } else if(!empty($account['error']) || !empty($jsResource['error'])) {
            $cloudConsult['cloud_consult_is_buy'] = 0;
        } else {
            $cloudConsult['cloud_consult_is_buy'] = 1;
            $cloudConsult['cloud_consult_login_url'] = $account['login_url'];
            $cloudConsult['cloud_consult_js'] = $jsResource['js_resource'];
        }

        $cloudConsult = array_merge($defaultSetting, $cloudConsult);

        return $cloudConsult;
    }

    public function getSettingService()
    {
        return $this->createService('System.SettingService');
    }
}