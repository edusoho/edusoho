<?php

namespace Biz\S2B2C;

use Biz\Course\Service\CourseService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Codeages\Biz\Framework\Service\BaseService;
use QiQiuYun\SDK\Auth;

class AbstractPlatformApi extends BaseService
{
    // 签名允许调用时效
    const LIFE_TIME = 30;

    protected $apiValid = true;

    protected $host;

    protected $uri;

    protected function request($method, $params = [], $options = [])
    {
        $url = $this->host.$this->uri;

        $options['userAgent'] = isset($options['userAgent']) ? $options['userAgent'] : '';
        $options['connectTimeout'] = isset($options['connectTimeout']) ? $options['connectTimeout'] : 10;
        $options['timeout'] = isset($options['timeout']) ? $options['timeout'] : 10;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $options['userAgent']);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $options['connectTimeout']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $options['timeout']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        $setting = $this->getSettingService()->get('storage', []);
        if (empty($setting['cloud_access_key'])) {
            $params['merchantAccessKey'] = $setting['cloud_access_key'];
        }
        $body = '';
        if ('POST' == $method) {
            curl_setopt($curl, CURLOPT_POST, 1);
            $body = $this->jsonEncode($params);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif ('PUT' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->jsonEncode($params));
        } elseif ('DELETE' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->jsonEncode($params));
        } elseif ('PATCH' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->jsonEncode($params));
        } else {
            if (!empty($options['params']) && 'body' == $options['params']) {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                $body = $this->jsonEncode($params);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            } else {
                if (!empty($params)) {
                    $transParams = (strpos($url, '?') ? '&' : '?').http_build_query($params);
                    $this->uri .= $transParams;
                    $url .= $transParams;
                }
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        $options = $this->makeAuthorization($options, $body);
        if (!empty($options['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
        }
        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);

        $body = substr($response, $curlinfo['header_size']);
        curl_close($curl);

        if (empty($curlinfo['namelookup_time'])) {
            return [];
        }

        if (isset($options['contentType']) && 'plain' == $options['contentType']) {
            return $body;
        }

        $body = json_decode($body, true);

        return $body;
    }

    protected function makeAuthorization($options, $body)
    {
        $storageSetting = $this->getSettingService()->get('storage', []);

        if (empty($storageSetting['cloud_access_key']) || empty($storageSetting['cloud_secret_key'])) {
            $this->getLogger()->error('makeAuthorization error: cloud_access_key or cloud_secret_key not exist', ['DATA' => $storageSetting]);

            return $options;
        }

        $auth = new Auth($storageSetting['cloud_access_key'], $storageSetting['cloud_secret_key']);

        $options['headers'][] = 'Authorization:'.$auth->makeRequestAuthorization($this->uri, $body, self::LIFE_TIME);
        $options['headers'][] = 'Accept:application/vnd.edusoho.v2+json';
        $options['headers'][] = 'Project-V:s2b2c.v1';

        return $options;
    }

    protected function jsonEncode($params)
    {
        $data = '';
        if (!empty($params)) {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                $data = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                $data = json_encode($params);
            }
        }

        return $data;
    }

    protected function createErrorResult($message = 'unexpected error')
    {
        return ['error' => $message];
    }

    protected function getLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->biz->service('S2B2C:S2B2CFacadeService');
    }
}
