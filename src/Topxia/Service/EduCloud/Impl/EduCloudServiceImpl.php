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

    public function checkSms($request, $scenario, $allowedTime = 1800)
    {
        $smsType = $request->getSession()->get('sms_type');

        if ((strlen($smsType) == 0) || (strlen($scenario) == 0)) {
            return false;
        }
        if ($smsType != $scenario) {
            return false;
        }

        $currentTime = time();
        $smsLastTime = $request->getSession()->get('sms_last_time');
        if ((strlen($smsLastTime) == 0) || (($currentTime - $smsLastTime) > $allowedTime)) {
            return false;
        }

        $smsCode = $request->getSession()->get('sms_code');
        $smsCodePosted = $request->request->get('sms_code');
        if ((strlen($smsCodePosted) == 0) || (strlen($smsCode) == 0)) {
            return false;
        }

        return ($smsCode == $smsCodePosted);
    }
}
