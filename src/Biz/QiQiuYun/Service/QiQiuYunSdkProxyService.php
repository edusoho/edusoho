<?php

namespace Biz\QiQiuYun\Service;

interface QiQiuYunSdkProxyService
{
    public function pushEventTracking($action, $data = null);
}
