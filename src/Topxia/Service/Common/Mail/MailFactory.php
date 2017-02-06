<?php
namespace Topxia\Service\Common\Mail;

use Topxia\Service\Common\ServiceKernel;

class MailFactory
{
    /**
     * @To Do fix cloud_email_crm
     *
     * @return CloudMail|NormalMail
     */
    public static function create($mailOptions)
    {
        $setting = ServiceKernel::instance()->createService('System.SettingService');

        $cloudConfig = $setting->get('cloud_email_crm', array());

        if (isset($cloudConfig['status']) && $cloudConfig['status'] == 'enable') {
            $mail = new CloudMail($mailOptions);
        } else {
            $mail = new NormalMail($mailOptions);
        }
        return $mail;
    }
}