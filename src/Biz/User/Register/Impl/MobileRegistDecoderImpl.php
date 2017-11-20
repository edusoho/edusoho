<?php

namespace Biz\User\Register\Impl;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use AppBundle\Common\SimpleValidator;

class MobileRegistDecoderImpl extends RegistDecoder
{
    protected function validateBeforeSave($registration, $type)
    {
        if (!empty($registration['mobile']) && !SimpleValidator::mobile($registration['mobile'])) {
            throw new InvalidArgumentException('Invalid Mobile');
        }
    }
}
