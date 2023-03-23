<?php

namespace Biz\BehaviorVerification\Service;

interface BehaviorVerificationService
{
    public function behaviorVerification($request);

    public function isInBlackIpList($ip);

    public function addBlackIpList($ip);
}