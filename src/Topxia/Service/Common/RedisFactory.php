<?php
namespace Topxia\Service\Common;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;

class RedisFactory
{
	private $container;
	private $redisPool;

	public function __construct($container)
	{
		$this->container = $container;
	}

	public function getRedis($group = 'default')
    {
        $redisPool = $this->getRedisPool();
        if($redisPool) {
            return $redisPool->getRedis($group);
        }
        return false;
    }

    private function getRedisPool()
    {
        if (isset($this->redisPool)) {
            return $this->redisPool;
        }

        $redisConfigFile = $this->container->getParameter('kernel.root_dir') . '/config/redis.php';

        if(file_exists($redisConfigFile)){
            $redisConfig = include $redisConfigFile;
            $this->redisPool = RedisPool::init($redisConfig);
            return $this->redisPool;
        }

        return false;
    }
}