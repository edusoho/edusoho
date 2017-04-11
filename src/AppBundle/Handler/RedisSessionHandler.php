<?php

/*
 * This file is part of the Symfony package.
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Handler;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

/**
 * RedisSessionHandler.
 */
class RedisSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \Redis driver
     */
    private $redis;

    /**
     * @var int Time to live in seconds
     */
    private $ttl;

    /**
     * @var string key prefix for shared environments
     */
    private $prefix;

    /**
     * @var TokenStorage
     */
    private $storage;

    /**
     * RedisSessionHandler constructor.
     * List of available options:
     *  prefix: The prefix to use for the redis keys in order to avoid collision
     *  expiretime: The time to live in seconds.
     *
     * @param              $biz
     * @param TokenStorage $storage
     * @param array        $options
     */
    public function __construct($factory, TokenStorage $storage, array $options = array())
    {
        if ($diff = array_diff(array_keys($options), array('prefix', 'expiretime'))) {
            throw new \InvalidArgumentException(sprintf(
                'The following options are not supported "%s"', implode(', ', $diff)
            ));
        }

        $this->redis = $factory->getRedis();
        $this->ttl = isset($options['expiretime']) ? (int) $options['expiretime'] : 86400;
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : 'es3_sess';

        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        return $this->redis->get($this->prefix.':'.$sessionId) ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        $time = time();
        if ($this->getCurrentUserId() > 0) {
            $this->redis->zAdd($this->prefix.':logined', $time, $sessionId);
            $this->redis->setTimeout($this->prefix.':logined', $this->ttl);
        }

        $this->redis->zAdd($this->prefix.':online', $time, $sessionId);
        $this->redis->setTimeout($this->prefix.':online', $this->ttl);

        $this->redis->setex($this->prefix.':'.$sessionId, $this->ttl, $data);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $this->redis->delete($this->prefix.':'.$sessionId);
        $this->redis->zRem($this->prefix.':logined', $sessionId);
        $this->redis->zRem($this->prefix.':online', $sessionId);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        $end = time() - $this->ttl;
        $start = 0;
        $this->redis->zRemRangeByScore($this->prefix.':logined', $start, $end);
        $this->redis->zRemRangeByScore($this->prefix.':online', $start, $end);

        return true;
    }

    private function getCurrentUserId()
    {
        $token = $this->storage->getToken();

        if (empty($token) || ($token instanceof AnonymousToken) || !$token->getUser()) {
            $userId = 0;
        } else {
            $userId = $token->getUser()->getId();
        }

        return $userId;
    }
}
