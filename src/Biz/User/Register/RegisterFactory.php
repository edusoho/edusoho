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

    public function createRegister($types)
    {
        $register = null;
        foreach ($types as $type) {
            $currentRegister = $this->biz['user.register.'.$type];
            $currentRegister->clearRegister();

            if (!empty($register)) {
                $currentRegister->setRegister($register);
            }
            $register = $currentRegister;
        }

        return $register;
    }
}
