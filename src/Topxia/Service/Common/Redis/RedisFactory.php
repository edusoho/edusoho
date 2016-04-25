<?php
namespace Topxia\Service\Common\Redis;

use Topxia\Service\Common\ServiceKernel;

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
        if (empty(self::$instance)) {
            self::$instance = new self($container);
        }

        return self::$instance;
    }

    public function getRedis($group = 'default')
    {
        if (empty($this->redis)) {
            $redisPool = $this->getRedisPool();

            if ($redisPool) {
                $this->redis = $redisPool->getRedis($group);
                return $this->redis;
            }

            return false;
        }

        return $this->redis;
    }

    private function getRedisPool()
    {
        $redisConfigFile = $this->container->getParameter('kernel.root_dir').'/data/redis.php';

        if (file_exists($redisConfigFile)) {
            $redisConfig     = include $redisConfigFile;
            $this->redisPool = RedisPool::init($redisConfig);
            return $this->redisPool;
        }

        return false;
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('Topxia.Basis:System.SettingService');
    }
}
