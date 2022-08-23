<?php

namespace Biz\SmsBlackCoordinate\Service;

interface SmsBlackCoordinateService
{
    public function isInBlackList($coordinate);
}
