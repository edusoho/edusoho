<?php

namespace ApiBundle\Security\RateLimit;

use Symfony\Component\HttpFoundation\Request;

interface RateLimiterInterface
{
    const PASS = 30000;

    const CAPTCHA_OCCUR = 30001;

    const MAX_REQUEST_OCCUR = 30002;

    public function handle(Request $request);
}