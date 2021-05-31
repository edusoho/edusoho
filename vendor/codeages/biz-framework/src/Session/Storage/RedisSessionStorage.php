<?php

namespace Codeages\Biz\Framework\Session\Storage;

use Redis;

class RedisSessionStorage implements SessionStorage
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    /**
     * @param $sessId
     * @return mixed
     * For php-redis 5.0.0+ function Redis::delete() is deprecated , replaced with Redis::del() compatible.
     * doc https://pecl.php.net/package-changelog.php?package=redis&release=2.2.3
     */
    public function delete($sessId)
    {
        $redis = $this->getRedis();
        if (method_exists($redis, 'del')) {
            return $redis->del($this->getSessionPrefix().':'.$sessId);
        }

        return $redis->delete($this->getSessionPrefix().':'.$sessId);
    }

    public function get($sessId)
    {
        return $this->getRedis()->get($this->getSessionPrefix().':'.$sessId);
    }

    public function save($session)
    {
        $session['sess_time'] = time();
        $this->getRedis()->setex($this->getSessionPrefix().':'.$session['sess_id'], $this->getMaxLifeTime(), $session);

        return $session;
    }

    public function gc()
    {
        return true;
    }

    protected function getSessionPrefix()
    {
        return 'biz_session_';
    }

    protected function getMaxLifeTime()
    {
        return $this->biz['session.options']['max_life_time'];
    }

    protected function getRedis()
    {
        return $this->getSessionRedis() ?: $this->biz['redis'];

        // return $this->biz['redis'];
    }

    protected function getSessionRedis()
    {
        if (empty($this->biz['session.redis.options'])) {
            return null;
        }

        $options = $this->biz['session.redis.options'];

        $redis = new Redis();
        $redis->connect($options['host'], $options['port'], $options['timeout'], $options['reserved'], $options['retry_interval']);
        $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        if ($options['key_prefix']) {
            $redis->setOption(Redis::OPT_PREFIX, $options['key_prefix']);
        }
        if (!empty($options['password'])) {
            $redis->auth($options['password']);
        }

        return $redis;
    }
}
