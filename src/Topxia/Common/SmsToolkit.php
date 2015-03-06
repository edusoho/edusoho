<?php
namespace Topxia\Common;

class SmsToolkit
{
	public static function paramForSmsCheck($request)
    {
        $sessionField['sms_type'] = $request->getSession()->get('sms_type');
        $sessionField['sms_last_time'] = $request->getSession()->get('sms_last_time');
        $sessionField['sms_code'] = $request->getSession()->get('sms_code');
        $sessionField['to'] = $request->getSession()->get('to');

        $requestField['sms_code'] = $request->request->get('sms_code');
        $requestField['mobile'] = $request->request->get('mobile');

        return array($sessionField, $requestField);
    }

    public static function clearSmsSession($request)
    {
        $request->getSession()->set('to',rand(0,999999));
        $request->getSession()->set('sms_code',rand(0,999999));
        $request->getSession()->set('sms_last_time','');
        $request->getSession()->set('sms_type', rand(0,999999));
    }
 
}