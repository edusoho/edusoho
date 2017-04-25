<?php

namespace Biz\Mail\Template;

class EmailSystemSelfTestTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('【%s】系统自检邮件', $this->getSiteName()),
            'body' => '系统邮件发送检测测试，请不要回复此邮件！',
        );
    }
}
