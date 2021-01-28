<?php

namespace Codeages\Biz\Framework\Context;

/**
 * 当前系统中的登录用户
 */
class CurrentUser implements \ArrayAccess
{
    /**
     * Data
     *
     * @var array
     */
    private $user = array();

    public function __construct(array $user)
    {
        $this->requireds($user, array('id', 'username', 'login_client', 'login_ip'));
        $this->user = $user;
    }

    public function offsetSet($key, $value)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('Key is empty, set current user attribute failed.');
        }

        if (array_key_exists($key, $this->user)) {
            throw new \LogicException('Key is already exist, set current user attribute failed.');
        }

        $this->user[$key] = $value;
    }

    public function offsetUnset($key)
    {
        throw new \LogicException('can not unset current user attribute.');
    }

    public function offsetExists($key)
    {
        return array_key_exists($key, $this->user);
    }

    public function offsetGet($key)
    {
        if (!$this->offsetExists($key)) {
            throw new \LogicException("Current user not have `{$key}` attribute.");
        }

        return $this->user[$key];
    }

    public function toArray()
    {
        return $this->user;
    }

    protected function requireds($user, $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $user)) {
                throw new \InvalidArgumentException("{$key} is missing.");
            }
        }
    }
}
