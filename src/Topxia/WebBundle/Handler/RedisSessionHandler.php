<?php

/*
 * This file is part of the Symfony package.
 *
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Topxia\WebBundle\Handler;

/**
 * RedisSessionHandler.
 *
 */
class RedisSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \Redis driver.
     */
    private $redis;

    /**
     * @var int Time to live in seconds
     */
    private $ttl;

    /**
     * @var string Key prefix for shared environments.
     */
    private $prefix;

    /**
     * Constructor.
     *
     * List of available options:
     *  * prefix: The prefix to use for the redis keys in order to avoid collision
     *  * expiretime: The time to live in seconds
     *
     * @param \Redis $redis A \Redis instance
     * @param array     $options  An associative array of Redis options
     *
     * @throws \InvalidArgumentException When unsupported options are passed
     */
    public function __construct($redisFactory, array $options = array())
    {
        if ($diff = array_diff(array_keys($options), array('prefix', 'expiretime'))) {
            throw new \InvalidArgumentException(sprintf(
                'The following options are not supported "%s"', implode(', ', $diff)
            ));
        }
        $this->redis = $redisFactory->getRedis();
        $this->ttl = isset($options['expiretime']) ? (int) $options['expiretime'] : 2*60*60;
        $this->prefix = isset($options['prefix']) ? $options['prefix'] : 'sf2s';
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
        return $this->redis->close();
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
        return $this->redis->setex($this->prefix.':'.$sessionId, $this->ttl, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        return $this->redis->delete($this->prefix.':'.$sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        // not required here because memcache will auto expire the records anyhow.
        return true;
    }
}
