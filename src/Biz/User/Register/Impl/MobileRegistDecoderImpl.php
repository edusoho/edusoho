<?php

namespace Biz\User\Register\Impl;

use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use AppBundle\Common\SimpleValidator;

class MobileRegistDecoderImpl extends RegistDecoder
{
    public function validateBeforeSave($registration, $type)
    {
        if (isset($registration['mobile']) && $registration['mobile'] != '' && !SimpleValidator::mobile($registration['mobile'])) {
            throw new InvalidArgumentException('Invalid Mobile');
        }
    }
}
