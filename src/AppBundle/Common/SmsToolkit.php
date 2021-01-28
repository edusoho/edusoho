<?php

namespace AppBundle\Common;

use Symfony\Component\HttpFoundation\Request;

class SmsToolkit
{
    private static $mockedRequest = null;

    public static function smsCheck($request, $scenario)
    {
        $mobile = $request->request->get('mobile');
        $postSmsCode = $request->request->get('sms_code');
        $ratelimiterResult = self::smsCheckRatelimiter($request, $scenario, $postSmsCode);
        if ($ratelimiterResult && false === $ratelimiterResult['success']) {
            return array(false, null, null);
        }

        list($sessionField, $requestField) = self::paramForSmsCheck($request, $scenario);
        $result = self::checkSms($sessionField, $requestField, $scenario);
        self::clearSmsSession($request, $scenario);

        return array($result, $sessionField, $requestField);
    }

    public static function smsCheckRatelimiter(Request $request, $type, $smsCode)
    {
        $smsSession = $request->getSession()->get($type);
        $smsSessionCode = $smsSession['sms_code'];

        if (!isset($smsSession['sms_remain'])) {
            $smsSession['sms_remain'] = 5;
        }
        $remain = $smsSession['sms_remain'];

        if ($smsSessionCode != $smsCode) {
            $remain = (int) $remain - 1;
            self::updateSmsSessionRemain($request, $type, $remain);
        }
        if (0 == $remain) {
            self::clearSmsSession($request, $type);

            return array('success' => false, 'message' => '错误次数已经超过最大次数，请重新获取');
        }

        return array('success' => true);
    }

    public static function updateSmsSessionRemain(Request $request, $type, $remain)
    {
        $smsSmsSession = $request->getSession()->get($type);
        $request->getSession()->set($type, array(
            'to' => $smsSmsSession['to'],
            'sms_code' => $smsSmsSession['sms_code'],
            'sms_last_time' => $smsSmsSession['sms_last_time'],
            'sms_type' => $type,
            'sms_remain' => $remain,
        ));
    }

    private static function paramForSmsCheck(Request $request, $scenario)
    {
        $sessionField = $request->getSession()->get($scenario);
        $sessionField['sms_type'] = $scenario;

        $requestField['sms_code'] = $request->request->get('sms_code');
        $requestField['mobile'] = $request->request->get('mobile');

        return array($sessionField, $requestField);
    }

    /**
     * @param array $sessionField 必须包含元素：'sms_type' 'sms_last_time' 'sms_code' 'to'
     * @param array $requestField 必须包含元素：'sms_code' 'mobile'
     *
     * @return bool
     */
    private static function checkSms($sessionField, $requestField, $scenario, $allowedTime = 1800)
    {
        $smsType = $sessionField['sms_type'];
        if ((0 == strlen($smsType)) || (0 == strlen($scenario))) {
            return false;
        }
        if ($smsType != $scenario) {
            return false;
        }

        $currentTime = time();
        $smsLastTime = $sessionField['sms_last_time'];
        if ((0 == strlen($smsLastTime)) || (($currentTime - $smsLastTime) > $allowedTime)) {
            return false;
        }

        $smsCode = $sessionField['sms_code'];
        $smsCodePosted = $requestField['sms_code'];
        if ((0 == strlen($smsCodePosted)) || (0 == strlen($smsCode))) {
            return false;
        }
        if ($smsCode != $smsCodePosted) {
            return false;
        }

        $to = $sessionField['to'];
        $mobile = $requestField['mobile'];
        if ((0 == strlen($to)) || (0 == strlen($mobile))) {
            return false;
        }
        if ($to != $mobile) {
            return false;
        }

        return true;
    }

    public static function clearSmsSession(Request $request, $scenario)
    {
        $request->getSession()->set($scenario, array(
            'to' => '',
            'sms_code' => '',
            'sms_last_time' => '',
            'sms_type' => '',
        ));
    }

    public static function getShortLink($url, $conditions = array())
    {
        // 为方便单元测试
        if (!empty(self::$mockedRequest)) {
            return self::$mockedRequest->getShortLink($url, $conditions);
        }
        $apis = array(
            'eduCloud' => 'http://kzedu.cc/app/shorturl',
            'baidu' => 'http://dwz.cn/create.php',
            'qq' => 'http://qqurl.com/create/',
        );

        foreach ($apis as $key => $api) {
            $response = CurlToolkit::request('POST', $api, array('url' => $url), $conditions);
            if ('eduCloud' == $key) {
                if (isset($response['short_url'])) {
                    return $response['short_url'];
                } else {
                    continue;
                }
            }

            if (0 != $response['status']) {
                continue;
            }

            if ('baidu' == $key) {
                return $response['tinyurl'];
            }

            if ('qq' == $key) {
                return $response['short_url'];
            }
        }

        return '';
    }
}
