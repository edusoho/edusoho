<?php
namespace Fomalhaut\Service\Wechat\Impl;

use Fomalhaut\Service\Wechat\AccountService;
use Topxia\Service\Common\BaseService;

class AccountServiceImpl extends BaseService implements AccountService
{
    public function addUniAccount($uniAcct)
    {
        $this->getUniAccountDao()->addUniAccount($uniAcct);
    }

    private function getUniAccountDao ()
    {
        return $this->createDao('Fomalhaut:Wechat.UniAccountDao');
    }
}