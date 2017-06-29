<?php

namespace Biz\Mail\Template;

class EmailRegistrationTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        $emailTitle = $this->setting('auth.email_activation_title', '请激活你的帐号 完成注册');
        $emailBody = $this->setting('auth.email_activation_body', ' 验证邮箱内容');
        $params = $options['params'];
        $valuesToReplace = array($params['nickname'], $params['sitename'], $params['siteurl'], $params['verifyurl']);
        $valuesToBeReplace = array('{{nickname}}', '{{sitename}}', '{{siteurl}}', '{{verifyurl}}');

        $emailTitle = str_replace($valuesToBeReplace, $valuesToReplace, $emailTitle);
        $emailBody = str_replace($valuesToBeReplace, $valuesToReplace, $emailBody);

        return array(
            'title' => $emailTitle,
            'body' => $emailBody,
        );
    }
}
