<?php

namespace Biz\BehaviorVerification\Service;

interface BehaviorVerificationCoordinateService
{
    public function isRobot($coordinate);

    public function decryptCoordinate($coordinate);
}
