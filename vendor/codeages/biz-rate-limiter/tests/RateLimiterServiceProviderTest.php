<?php
namespace Codeages\Biz\Targetlog\Tests;

use Codeages\Biz\Framework\UnitTests\BaseTestCase;
use Codeages\Biz\RateLimiter\RateLimiterServiceProvider;

class RateLimiterServiceProviderTestCase extends BaseTestCase
{
    public function testIndex()
    {
        $provider = new RateLimiterServiceProvider();
        self::$biz->register($provider);

        $factory = self::$biz['ratelimiter.factory'];
        $limiter = $factory('test', 10, 600);

        $this->assertInstanceOf('Codeages\RateLimiter\RateLimiter', $limiter);
    }

}