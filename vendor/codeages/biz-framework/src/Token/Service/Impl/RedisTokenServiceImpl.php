<?php

namespace Codeages\Biz\Framework\Token\Service\Impl;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Token\Service\GenerateException;
use Codeages\Biz\Framework\Token\Service\TokenService;
use Codeages\Biz\Framework\Service\BaseService;
use Webpatser\Uuid\Uuid;

class RedisTokenServiceImpl extends BaseService implements TokenService
{
    /**
     * @var \Redis|\RedisArray
     */
    protected $redis;

    public function __construct(Biz $biz)
    {
        parent::__construct($biz);

        $pool = isset($this->biz['token_service.redis.pool']) ? $this->biz['token_service.redis.pool'] : null;
        if ($pool) {
            $this->redis = $this->biz['mult_redis'][$pool];
        } else {
            $this->redis = $this->biz['redis'];
        }
    }

    public function generate($place, $lifetime, $times = 0, $data = null)
    {
        $token = array();
        $token['place'] = $place;
        $token['key'] = str_replace('-', '', Uuid::generate(4));
        $token['data'] = $data;
        $token['expired_time'] = empty($lifetime) ? 0 : time() + $lifetime;
        $token['times'] = $times;
        $token['remaining_times'] = $times;
        $token['created_time'] = time();

        $key = $this->key($place, $token['key']);
        $seted = $this->redis->setnx($key, $token);
        if (!$seted) {
            throw new GenerateException("Generate Token Failed(key:{$token['key']}.");
        }

        if ($lifetime) {
            $this->redis->expire($key, $lifetime);
        }

        return $token;
    }

    public function verify($place, $key)
    {
        $key = $this->key($place, $key);
        $token = $this->redis->get($key);

        if (empty($token)) {
            return false;
        }

        if ($token['times'] > 0 && ($token['remaining_times'] < 1)) {
            $this->redis->del($key);

            return false;
        }

        if ($token['remaining_times'] >= 1) {
            $token['remaining_times'] = $token['remaining_times'] - 1;
            if ($token['expired_time'] > 0) {
                $ttl = $token['expired_time'] - time();
                if ($ttl <= 0) {
                    $this->redis->del($key);
                }
            } else {
                $ttl = 0;
            }

            if ($ttl > 0) {
                $this->redis->set($key, $token, $ttl);
            } else {
                $this->redis->set($key, $token);
            }
        }

        if ($token['times'] > 0 && 0 == $token['remaining_times']) {
            $this->redis->del($key);
        }

        return $token;
    }

    public function destroy($place, $key)
    {
        return $this->redis->del($this->key($place, $key));
    }

    public function gc()
    {
        return;
    }

    protected function key($place, $key)
    {
        return "biz:token:{$place}:{$key}";
    }
}
