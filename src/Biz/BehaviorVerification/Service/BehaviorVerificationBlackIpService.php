<?php

namespace Biz\BehaviorVerification\Service;

interface BehaviorVerificationBlackIpService
{
    public function isInBlackIpList($ip);

    public function addBlackIpList($ip);
}
