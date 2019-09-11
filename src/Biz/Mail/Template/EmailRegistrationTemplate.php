<?php

namespace Biz\Mail\Template;

class EmailRegistrationTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        /** 由于分校插件重写settingService，此处不传入默认值 */
        $emailTitle = $this->setting('auth.email_activation_title');
        $emailBody = $this->setting('auth.email_activation_body');
        $params = $options['params'];
        $valuesToReplace = array($params['nickname'], $params['sitename'], $params['siteurl'], $params['verifyurl']);
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}', '{{verifyurl}}');

        $emailTitle = str_replace($valuesToBeReplace, $valuesToReplace, $emailTitle);
        $emailBody = str_replace($valuesToBeReplace, $valuesToReplace, $emailBody);

        return array(
            'title' => empty($emailTitle) ? '请激活你的帐号 完成注册' : $emailTitle,
            'body' => empty($emailBody) ? '验证邮箱内容' : $emailBody,
        );
    }
}
