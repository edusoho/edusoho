<?php

namespace Biz\User\Service\Impl;

use Biz\BaseService;
use Biz\User\Service\TokenService;

class TokenServiceImpl extends BaseService implements TokenService
{
    public function makeToken($type, array $args = array())
    {
        $token = array();
        $token['type'] = $type;
        $token['token'] = $this->_makeTokenValue(32);
        $token['data'] = !isset($args['data']) ? '' : $args['data'];
        $token['times'] = empty($args['times']) ? 0 : (int) $args['times'];
        $token['remainedTimes'] = $token['times'];
        $token['userId'] = empty($args['userId']) ? 0 : $args['userId'];
        $token['expiredTime'] = empty($args['duration']) ? 0 : time() + $args['duration'];
        $token['createdTime'] = time();

        return $this->getTokenDao()->create($token);
    }

    public function makeFakeTokenString($length = 32)
    {
        return $this->_makeTokenValue($length);
    }

    public function verifyToken($type, $value, array $data = array())
    {
        $token = $this->getTokenDao()->getByToken($value);

        if (empty($token)) {
            return false;
        }

        if ($token['type'] != $type) {
            return false;
        }

        if (($token['expiredTime'] > 0) && ($token['expiredTime'] < time())) {
            return false;
        }

        if ($token['remainedTimes'] > 1) {
            $this->getTokenDao()->wave(array($token['id']), array('remainedTimes' => -1));
        }

        if (!empty($data)) {
            $token = $this->getTokenDao()->update($token['id'], array('data' => $data));
        }

        $this->_gcToken($token);

        return $token;
    }

    public function destoryToken($token)
    {
        $token = $this->getTokenDao()->getByToken($token);

        if (empty($token)) {
            return;
        }

        $this->getTokenDao()->delete($token['id']);
    }

    public function findTokensByUserIdAndType($userId, $type)
    {
        return $this->getTokenDao()->findByUserIdAndType($userId, $type);
    }

    public function getTokenByType($type)
    {
        return $this->getTokenDao()->getByType($type);
    }

    public function deleteTokenByTypeAndUserId($type, $userId)
    {
        return $this->getTokenDao()->deleteByTypeAndUserId($type, $userId);
    }

    public function deleteExpiredTokens($limit)
    {
        $this->getTokenDao()->deleteTopsByExpiredTime(time(), $limit);
    }

    protected function _gcToken($token)
    {
        if (($token['times'] > 0) && ($token['remainedTimes'] <= 1)) {
            $this->getTokenDao()->delete($token['id']);

            return;
        }

        if (($token['expiredTime'] > 0) && ($token['expiredTime'] < time())) {
            $this->getTokenDao()->delete($token['id']);

            return;
        }

        return;
    }

    protected function _makeTokenValue($length)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    protected function getTokenDao()
    {
        return $this->createDao('User:TokenDao');
    }
}
