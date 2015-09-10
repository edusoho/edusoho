<?php
namespace Topxia\Service\Common;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;

class RedisFactory
{
	private $container;
	private static $instance;
    private $redis;

    private function __construct($container)
    {
        $this->container = $container;
    }

    public static function instance($container)
    {
        if(empty(self::$instance)) {
            self::$instance = new RedisFactory($container);
        }
        return self::$instance;
	}

	public function getRedis($group = 'default')
    {
        if(empty($this->redis)){
            $redisPool = $this->getRedisPool();
            if($redisPool) {
                $this->redis = $redisPool->getRedis($group);
                return $this->redis;
            }
            return false;
        }

        return $this->redis;
    }

    private function getRedisPool()
    {

        $redisConfigFile = $this->container->getParameter('kernel.root_dir') . '/config/redis.php';

        if(file_exists($redisConfigFile)){
            $redisConfig = include $redisConfigFile;
            $this->redisPool = RedisPool::init($redisConfig);
            return $this->redisPool;
        }

        return false;
    }
}