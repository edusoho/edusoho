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

    public function getAccount()
    {
        $api = $this->getCloudApi();
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

    public function getSmsOpenStatus()
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

    public function getBills($type, $page, $limit)
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        $result = $api->get('/bills', $params = array('type' => $type, 'page' => $page, 'limit' => $limit));
        return $result;
    }

    public function getLiveCourseStatus()
    {
        $api = $this->getCloudApi();
        return $api->get('/lives/account');
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
