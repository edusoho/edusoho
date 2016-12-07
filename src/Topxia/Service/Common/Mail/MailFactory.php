<?php

namespace Topxia\Service\Common\Mail;
use Topxia\Service\Common\ServiceKernel;


class MailFactory
{
    public static function create($mailOptions)
    {
        $setting = ServiceKernel::instance()->createService('System.SettingService');

        $cloudConfig = $setting->get('cloud_email', array());

        if (isset($cloudConfig['status']) && $cloudConfig['status'] == 'enable') {
            $mail = new CloudMail($mailOptions);
        } else {
            $mail = new NormalMail($mailOptions);
        }
        return $mail;
    }
}