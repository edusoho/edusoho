<?php


namespace Topxia\Service\Common;


class MailFactory
{
    public static function create($normalOption, $cloudOptions)
    {
        $setting = ServiceKernel::instance()->createService('System.SettingService');

        $cloudConfig = $setting->get('cloud_email', array());

        if (isset($cloudConfig['status']) && $cloudConfig['status'] == 'enable') {
            $mail = new CloudMail($cloudOptions);
        } else {
            $mail = new NormalMail($normalOption);
        }
        return $mail;
    }
}