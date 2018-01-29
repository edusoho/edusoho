<?php

namespace Codeages\Biz\Framework\Token\Service\Impl;

use Codeages\Biz\Framework\Token\Dao\TokenDao;
use Codeages\Biz\Framework\Token\Service\TokenService;
use Codeages\Biz\Framework\Service\BaseService;
use Webpatser\Uuid\Uuid;

class DatabaseTokenServiceImpl extends BaseService implements TokenService
{
    public function generate($place, $lifetime, $times = 0, $data = null)
    {
        $token = array();
        $token['place'] = $place;
        $token['_key'] = str_replace('-', '', Uuid::generate(4));
        $token['data'] = $data;
        $token['expired_time'] = empty($lifetime) ? 0 : time() + $lifetime;
        $token['times'] = $times;
        $token['remaining_times'] = $times;
        $token['created_time'] = time();

        $token = $this->getTokenDao()->create($token);

        $this->gc();

        return $this->filter($token);
    }

    public function verify($place, $key)
    {
        $token = $this->getTokenDao()->getByKey($key);

        if (empty($token)) {
            return false;
        }

        if ($token['place'] != $place) {
            return false;
        }

        if (($token['expired_time'] > 0) && ($token['expired_time'] < time())) {
            $this->getTokenDao()->delete($token['id']);

            return false;
        }

        if ($token['times'] > 0 && ($token['remaining_times'] < 1)) {
            $this->getTokenDao()->delete($token['id']);

            return false;
        }

        if ($token['remaining_times'] >= 1) {
            $this->getTokenDao()->wave(array($token['id']), array('remaining_times' => -1));
            $token['remaining_times'] -= 1;
        }

        if ($token['times'] > 0 && 0 == $token['remaining_times']) {
            $this->getTokenDao()->delete($token['id']);
        }

        return $this->filter($token);
    }

    public function destroy($place, $key)
    {
        $token = $this->getTokenDao()->getByKey($key);
        if (empty($token)) {
            return;
        }

        $this->getTokenDao()->delete($token['id']);
    }

    protected function filter($token)
    {
        $token['key'] = $token['_key'];
        unset($token['_key']);
        unset($token['id']);

        return $token;
    }

    public function gc()
    {
        $divisor = $this->biz['token_service.gc_divisor'];
        if (empty($divisor)) {
            return;
        }

        $middle = intval((1 + $divisor) / 2);
        $rand = rand(1, $divisor);
        if ($rand != $middle) {
            return;
        }

        $this->getTokenDao()->deleteExpired(time());
    }

    /**
     * @return TokenDao
     */
    protected function getTokenDao()
    {
        return $this->biz->dao('Token:TokenDao');
    }
}
