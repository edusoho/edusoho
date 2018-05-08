<?php

namespace Biz\Mp\Service\Impl;

use Biz\BaseService;
use Biz\Mp\Service\MpService;

class MpServiceImpl extends BaseService implements MpService
{
    public function getMpSdk()
    {
        return $this->biz['qiQiuYunSdk.mp'];
    }
}
