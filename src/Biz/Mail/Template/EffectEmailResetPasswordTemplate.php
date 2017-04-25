<?php

namespace Biz\Mail\Template;

class EffectEmailResetPasswordTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('重置您的%s帐号密码', $this->getSiteName()),
            'body' => $this->renderBody('effect-reset.txt.twig', $options['params']),
        );
    }
}
