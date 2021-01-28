<?php

namespace AppBundle\Common;

use AppBundle\Common\Exception\AccessDeniedException;

class CurlToolkit
{
    private static $whiteList = array(
        'formula.edusoho.net', //公式编辑器
        'www.edusoho.com', //官网
        'open.edusoho.com', //open站
        'kzedu.cc', //eduCloud短链
        'dwz.cn', //百度短链
        'qqurl.com', //qq短链
        'api.edusoho.net', //云平台接口
    );

    public static function request($method, $url, $params = array(), $conditions = array())
    {
        $parseUrl = parse_url($url);
        if (!in_array($parseUrl['host'], self::$whiteList)) {
            throw new AccessDeniedException('url is not allowed!');
        }
        $conditions['userAgent'] = isset($conditions['userAgent']) ? $conditions['userAgent'] : '';
        $conditions['connectTimeout'] = isset($conditions['connectTimeout']) ? $conditions['connectTimeout'] : 10;
        $conditions['timeout'] = isset($conditions['timeout']) ? $conditions['timeout'] : 10;
        if (isset($_SERVER['TRACE_ID']) && $_SERVER['TRACE_ID']) {
            $conditions['headers'] = empty($conditions['headers']) ? array('TRACE-ID: '.$_SERVER['TRACE_ID']) : array_merge($conditions['headers'], array('TRACE-ID: '.$_SERVER['TRACE_ID']));
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $conditions['userAgent']);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $conditions['connectTimeout']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $conditions['timeout']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        if ('POST' == $method) {
            curl_setopt($curl, CURLOPT_POST, 1);
            //TODO
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        } elseif ('PUT' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('DELETE' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('PATCH' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (!empty($params)) {
                $url = $url.(strpos($url, '?') ? '&' : '?').http_build_query($params);
            }
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        if (!empty($conditions['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $conditions['headers']);
        }
        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);

        $header = substr($response, 0, $curlinfo['header_size']);
        $body = substr($response, $curlinfo['header_size']);

        curl_close($curl);

        if (empty($curlinfo['namelookup_time'])) {
            return array();
        }

        if (isset($conditions['contentType']) && 'plain' == $conditions['contentType']) {
            return $body;
        }

        $body = json_decode($body, true);

        return $body;
    }
}
