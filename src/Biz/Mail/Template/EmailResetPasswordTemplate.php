<?php

namespace Biz\Mail\Template;

class EmailResetPasswordTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('重设%s在%s的密码', $options['params']['nickname'], $this->getSiteName()),
            'body' => $this->renderBody('reset.txt.twig', $options['params']),
        );
    }
}
