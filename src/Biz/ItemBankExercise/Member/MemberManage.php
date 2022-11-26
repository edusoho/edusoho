<?php

namespace Biz\ItemBankExercise\Member;

use Codeages\Biz\Framework\Context\Biz;

class MemberManage
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $role
     * @return Member
     */
    public function getMemberClass($role)
    {
        $class = __NAMESPACE__.'\\'.ucfirst($role.'Member');

        return new $class($this->biz);
    }
}
