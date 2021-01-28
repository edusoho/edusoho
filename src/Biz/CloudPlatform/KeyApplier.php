<?php

namespace Biz\CloudPlatform;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\CurlToolkit;

class KeyApplier
{
    private $moked;

    public function applyKey($user, $edition = 'opensource', $source = 'apply')
    {
        $setting = $this->getSettingService()->get('storage', array());
        if (!empty($setting['cloud_access_key']) && !empty($setting['cloud_secret_key']) && !empty($setting['cloud_key_applied'])) {
            return array(
                'accessKey' => $setting['cloud_access_key'],
                'secretKey' => $setting['cloud_secret_key'],
            );
        }

        $profile = $this->getUserService()->getUserProfile($user['id']);

        $params = array();
        $site = $this->getSettingService()->get('site');

        $params['siteName'] = empty($site['name']) ? 'EduSoho网络课程' : $site['name'];
        $params['siteUrl'] = 'http://'.$_SERVER['HTTP_HOST'];
        $params['email'] = empty($user['email']) ? '' : $user['email'];
        $params['contact'] = empty($profile['truename']) ? '' : $profile['truename'];
        $params['qq'] = empty($profile['qq']) ? '' : $profile['qq'];
        $params['mobile'] = empty($profile['mobile']) ? '' : $profile['mobile'];
        $params['edition'] = empty($edition) ? 'opensource' : $edition;
        $params['source'] = empty($source) ? 'apply' : $source;
        $params['visitorId'] = empty($user['visitorId']) ? '' : $user['visitorId'];

        $url = empty($setting['cloud_api_server']) ? 'http://api.edusoho.net' : rtrim($setting['cloud_api_server'], '/');
        $url = $url.'/v1/keys';

        $curlOptions = array(
            'connectTimeout' => 20,
            'userAgent' => 'EduSoho Install Client 1.0',
            'timeout' => 20,
            'headers' => array(
                'Content-type: application/json',
                'Sign: '.md5(json_encode($params)),
            ),
        );

        $response = empty($this->moked) ? CurlToolkit::request('POST', $url, json_encode($params), $curlOptions) : array(
            'url' => $url,
            'params' => $params,
            'curlOptions' => $curlOptions,
        );
        if (empty($response)) {
            return array('error' => '生成Key失败，请检查服务器的网络设置！');
        }

        return $response;
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->getBiz()->service('User:UserService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
