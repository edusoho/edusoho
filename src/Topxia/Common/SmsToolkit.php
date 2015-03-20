<?php
namespace Topxia\Common;

class SmsToolkit
{
    public static function smsCheck($request, $scenario)
    {
        list($sessionField, $requestField) = self::paramForSmsCheck($request, $scenario);
        $result = self::checkSms($sessionField, $requestField, $scenario);
        self::clearSmsSession($request, $scenario);
        return array($result, $sessionField, $requestField);
    }

	private static function paramForSmsCheck($request, $scenario)
    {
        $sessionField = $request->getSession()->get($scenario);
        $sessionField['sms_type'] = $scenario;

        $requestField['sms_code'] = $request->request->get('sms_code');
        $requestField['mobile'] = $request->request->get('mobile');

        return array($sessionField, $requestField);
    }

    /**
     * @param  array $sessionField 必须包含元素：'sms_type' 'sms_last_time' 'sms_code' 'to'
     * @param  array $requestField 必须包含元素：'sms_code' 'mobile'
     * @return boolean
     */
    private static function checkSms($sessionField, $requestField, $scenario, $allowedTime = 1800)
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

    public static function clearSmsSession($request, $scenario)
    {
        $request->getSession()->set($scenario,array(
            'to' => '',
            'sms_code' => '',
            'sms_last_time' => '',
            'sms_type' => ''
        ));
    }
 
}