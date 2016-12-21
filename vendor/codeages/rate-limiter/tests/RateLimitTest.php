<?php

use Codeages\RateLimiter\RateLimiter;
use Codeages\RateLimiter\Storage\Storage;

class RateLimiterTest extends \PHPUnit_Framework_TestCase
{
    const NAME = 'RateLimiterTest';
    const MAX_REQUESTS = 10;
    const PERIOD = 3;

    public function testCheckRedis()
    {
        $storage = new \Codeages\RateLimiter\Storage\RedisStorage();
        $this->check($storage);
    }

    public function testCheckMySQLPDO()
    {
        $pdo = new PDO(getenv('MYSQL_DSN'), getenv('MYSQL_USER'), getenv('MYSQL_MYSQL_PASSWORD'));
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $storage = new \Codeages\RateLimiter\Storage\MySQLPDOStorage($pdo);
        $this->check($storage);
    }

    private function check($storage)
    {
        $ip = '127.0.0.1';
        $rateLimit = $this->getRateLimiter($storage);
        $rateLimit->ttl = 100;

        $this->assertEquals(self::MAX_REQUESTS, $rateLimit->getAllow($ip));

        //First
        $this->assertEquals(self::MAX_REQUESTS, $rateLimit->check($ip));

        //Repeat MAX_REQUESTS - 1 times
        for ($i = 0; $i < self::MAX_REQUESTS; ++$i) {
            $this->assertEquals(self::MAX_REQUESTS - $i - 1, $rateLimit->getAllow($ip));
            $this->assertEquals(self::MAX_REQUESTS - $i - 1, $rateLimit->check($ip));
        }

        //MAX_REQUESTS + 1
        $this->assertEquals(0, $rateLimit->getAllow($ip));
        $this->assertEquals(0, $rateLimit->check($ip));

        //Wait for PERIOD seconds
        sleep(self::PERIOD);
        $this->assertEquals(self::MAX_REQUESTS, $rateLimit->getAllow($ip));
        $this->assertEquals(self::MAX_REQUESTS, $rateLimit->check($ip));

        $rateLimit->purge($ip);
    }

    private function getRateLimiter(Storage $storage)
    {
        return new RateLimiter(self::NAME, self::MAX_REQUESTS, self::PERIOD, $storage);
    }
}
