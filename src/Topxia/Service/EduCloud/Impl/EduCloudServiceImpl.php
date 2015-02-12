<?php
namespace Topxia\Service\EduCloud\Impl;

use Topxia\Service\CloudPlatform\Client\CloudAPI;
use Topxia\Service\Common\BaseService;

class EduCloudServiceImpl extends BaseService
{
    private $cloudApi = null;
    private $cloudOptions = null;

    private function getCloudOptions()
    {
        if (empty($this->cloudOptions)) {
            $settings = $this->createService('System.SettingService')->get('storage', array());
            $this->cloudOptions = array(
                'accessKey' => empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'],
                'secretKey' => empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'],
                'apiUrl' => empty($settings['cloud_api_server']) ? '' : $settings['cloud_api_server'],
            );
        }
        return $this->cloudOptions;
    }

    private function createAPIClient()
    {
        $options = $this->getCloudOptions();
        return new CloudAPI($options);
    }

    private function getCloudApi()
    {
        if (empty($this->cloudApi)) {
            $this->cloudApi = $this->createAPIClient();
        }
        return $this->cloudApi;
    }

    public function getAccounts()
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        return $api->get('/accounts');
    }

    public function applyForSms($name = 'smsHead')
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();

        $result = $api->post(
            sprintf('/sms/%s/apply', $options['accessKey']),
            $params = array('name' => $name)
        );

        return $result;
    }

    public function lookForStatus()
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        $result = $api->post(
            sprintf('/sms/%s/applyResult', $options['accessKey'])
        );
        return $result;
    }

    public function sendSms($to, $verify, $category = 'verify')
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        $result = $api->post(
            sprintf('/sms/%s/sendVerify', $options['accessKey']),
            $params = array('mobile' => $to, 'verify' => $verify, 'category' => $category)
        );
        return $result;
    }

    public function verifyKeys()
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        $result = $api->post(
            sprintf('/keys/%s/verification', $options['accessKey'])
        );
        return $result;
    }

    /**
     * @param  array $sessionField 必须包含元素：'sms_type' 'sms_last_time' 'sms_code' 'to'
     * @param  array $requestField 必须包含元素：'sms_code' 'mobile'
     * @return boolean
     */
    public function checkSms($sessionField, $requestField, $scenario, $allowedTime = 1800)
    {
        $smsType = $sessionField['sms_type'];
        if ((strlen($smsType) == 0) || (strlen($scenario) == 0)) {
            return false;
        }
        if ($smsType != $scenario) {
            return false;
        }

        $currentTime = time();
        $smsLastTime = $sessionField['sms_last_time'];
        if ((strlen($smsLastTime) == 0) || (($currentTime - $smsLastTime) > $allowedTime)) {
            return false;
        }

        $smsCode = $sessionField['sms_code'];
        $smsCodePosted = $requestField['sms_code'];
        if ((strlen($smsCodePosted) == 0) || (strlen($smsCode) == 0)) {
            return false;
        }
        if ($smsCode != $smsCodePosted){
            return false;
        }

        $to = $sessionField['to'];
        $mobile = $requestField['mobile'];
        if ((strlen($to) == 0) || (strlen($mobile) == 0)) {
            return false;
        }
        if ($to != $mobile){
            return false;
        }        

        return true;
    }

    public function paramForSmsCheck($request)
    {
        $sessionField['sms_type'] = $request->getSession()->get('sms_type');
        $sessionField['sms_last_time'] = $request->getSession()->get('sms_last_time');
        $sessionField['sms_code'] = $request->getSession()->get('sms_code');
        $sessionField['to'] = $request->getSession()->get('to');

        $requestField['sms_code'] = $request->request->get('sms_code');
        $requestField['mobile'] = $request->request->get('mobile');

        return array($sessionField, $requestField);
    }

    public function clearSmsSession($request)
    {
        $request->getSession()->set('to',rand(0,999999));
        $request->getSession()->set('sms_code',rand(0,999999));
        $request->getSession()->set('sms_last_time','');
        $request->getSession()->set('sms_type', rand(0,999999));
    }

    public function getCloudSmsKey($key)
    {
        $setting = $this->createService('System.SettingService')->get('cloud_sms', array());
        if (isset($setting[$key])){
            return $setting[$key];
        }
        return null;
    }    
}
