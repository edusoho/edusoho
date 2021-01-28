<?php

namespace Biz\Mail\Template;

class EmptyTemplate implements EmailTemplateInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($options)
    {
        return array(
            'title' => empty($options['title']) ? '' : $options['title'],
            'body' => empty($options['body']) ? '' : $options['body'],
        );
    }
}
