<?php

namespace Biz\Mail\Template;

class EmailResetEmailTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('重设%s在%s的电子邮箱', $options['params']['nickname'], $this->getSiteName()),
            'body' => $this->renderBody('email-change.txt.twig', $options['params']),
        );
    }
}
