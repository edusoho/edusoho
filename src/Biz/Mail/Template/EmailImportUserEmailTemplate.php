<?php

namespace Biz\Mail\Template;

class EmailImportUserEmailTemplate extends BaseTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => sprintf('%s账号创建成功', $this->getSiteName()),
            'body' => $this->renderBody('email-import-user.txt.twig', $options['params']),
        );
    }
}
