<?php

namespace Biz\User\Register;

use Codeages\Biz\Framework\Context\Biz;

class RegisterFactory
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function createRegister($type)
    {
        if ($type == 'email') {
            return new EmailRegister($this->biz);
        }
    }
}
