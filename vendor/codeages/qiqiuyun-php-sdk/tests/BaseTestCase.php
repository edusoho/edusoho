<?php

namespace QiQiuYun\SDK\Tests;

use PHPUnit\Framework\TestCase;
use QiQiuYun\SDK\Auth;

abstract class BaseTestCase extends TestCase
{
    protected $accessKey = 'test_access_key';

    protected $secretKey = 'test_secret_key';

    public function createAuth()
    {
        return new Auth($this->accessKey, $this->secretKey);
    }
}
