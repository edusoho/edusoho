<?php

namespace Biz\PostFilter\Service;

interface TokenBucketService
{
    public function hasToken($ip, $type);

    public function incrToken($ip, $type);
}
