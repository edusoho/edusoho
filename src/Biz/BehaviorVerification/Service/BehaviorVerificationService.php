<?php

namespace Biz\BehaviorVerification\Service;

interface BehaviorVerificationService
{
    public function verificateBehavior($request);

    public function isInBlackIpList($ip);

    public function addBlackIpList($ip);
}
