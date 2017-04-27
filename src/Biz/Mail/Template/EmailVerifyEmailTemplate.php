<?php

namespace Biz\Mail\Template;

class EmailVerifyEmailTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('验证%s在%s的电子邮箱', $options['params']['nickname'], $this->getSiteName()),
            'body' => $this->renderBody('email-verify.txt.twig', $options['params']),
        );
    }
}
