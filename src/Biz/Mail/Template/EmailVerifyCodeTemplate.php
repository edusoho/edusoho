<?php

namespace Biz\Mail\Template;

class EmailVerifyCodeTemplate extends BaseTemplate implements EmailTemplateInterface
{
    public function parse($options)
    {
        return [
            'title' => sprintf('注册%s的验证码', $this->getSiteName()),
            'body' => $this->renderBody('email-verify-code.txt.twig', $options['params']),
        ];
    }
}
