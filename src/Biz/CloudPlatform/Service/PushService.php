<?php

namespace Biz\CloudPlatform\Service;

interface PushService
{
    public function push($from, $to, $body);
}
