<?php

namespace ApiBundle\Api\Resource\App;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Common\CommonException;
use Biz\System\Service\SettingService;

class AppSuggestion extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $info = $request->request->get('info');
        $type = $request->request->get('type', 'bug');
        $contact = $request->request->get('contact');

        if (empty($info)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $site = $this->getSettingService()->get('site');
        $storage = $this->getSettingService()->get('storage');

        $this->sendRequest('POST', 'http://demo.edusoho.com/mapi_v2/School/suggestionLog', array(
            'info' => $info,
            'type' => $type,
            'contact' => $contact,
            'domain' => $site['url'],
            'accessKey' => $storage['cloud_access_key'],
            'name' => $site['name'],
        ));

        return array('success' => true);
    }

    private function sendRequest($method, $url, $params = array(), $ssl = false)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, 'Suggestion Request');

        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 20);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ('POST' == strtoupper($method)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            $params = http_build_query($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
