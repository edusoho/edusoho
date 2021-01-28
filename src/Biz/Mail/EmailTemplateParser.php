<?php

namespace Biz\Mail;

use Codeages\Biz\Framework\Context\BizAware;

class EmailTemplateParser extends BizAware
{
    public function parseTemplate($templateName, $arguments)
    {
        if (isset($this->biz[$templateName.'_template'])) {
            return $this->biz[$templateName.'_template']->parse($arguments);
        }

        return $this->biz['empty_email_template']->parse($arguments);
    }
}
