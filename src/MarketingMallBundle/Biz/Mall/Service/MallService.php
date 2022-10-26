<?php

namespace MarketingMallBundle\Biz\Mall\Service;

interface MallService
{
    public function isShow();

    public function isInit();

    public function init($userInfo, $url);
}
