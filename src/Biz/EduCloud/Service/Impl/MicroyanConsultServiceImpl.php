<?php

namespace Biz\EduCloud\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\EduCloud\Service\ConsultService;
use Biz\System\Service\SettingService;

class MicroyanConsultServiceImpl extends BaseService implements ConsultService
{
    private $cloudApi = null;

    public function getAccount()
    {
        return $this->createCloudApi()->post('/robot/login_url');
    }

    public function getJsResource()
    {
        return $this->createCloudApi()->post('/robot/install');
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

        if (!empty($account['code']) || !empty($jsResource['code']) || !empty($account['error']) || !empty($jsResource['error'])) {
            $cloudConsult['cloud_consult_is_buy'] = 0;

            $accountCode = empty($account['code']) ? '' : $account['code'];
            $jsResourceCode = empty($jsResource['code']) ? '' : $jsResource['code'];

            if ('10001' == $accountCode || '10001' == $jsResourceCode) {
                $cloudConsult['error'] = '帐号已过期,请联系客服人员:4008041114！';
            }
        } else {
            $cloudConsult['cloud_consult_is_buy'] = 1;
            $cloudConsult['cloud_consult_login_url'] = $account['loginUrl'];
            $cloudConsult['cloud_consult_js'] = $jsResource['install'];
        }

        return array_merge($defaultSetting, $cloudConsult);
    }

    /**
     * only for mock.
     *
     * @param [type] $cloudApi [description]
     */
    public function setCloudApi($cloudApi)
    {
        return $this->cloudApi = $cloudApi;
    }

    protected function createCloudApi()
    {
        if (empty($this->cloudApi)) {
            $this->cloudApi = CloudAPIFactory::create('root');
        }

        return $this->cloudApi;
    }

    /**
     * @return SettingService
     */
    public function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
