<?php

namespace Biz\Mail\Template;

class EmailVerifyCodeTemplate extends BaseTemplate implements EmailTemplateInterface
{
    public function parse($options)
    {
        return [
            'title' => sprintf('【%s】邮箱验证码', $this->getSiteName()),
            'body' => $this->renderBody('email-verify-code.html.twig', $options['params']),
        ];
    }
}
