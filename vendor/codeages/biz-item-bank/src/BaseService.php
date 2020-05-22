<?php

namespace Codeages\Biz\ItemBank;

class BaseService extends \Codeages\Biz\Framework\Service\BaseService
{
    protected function getValidator()
    {
        $this->addValidatorRule();

        return $this->biz['validator'];
    }

    protected function addValidatorRule()
    {
    }
}
