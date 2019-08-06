<?php

namespace Codeages\Biz\Framework\Util;

/**
 * @deprecated 2.0
 */
class Lock
{
    private $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    public function get($lockName, $lockTime = 30)
    {
        $result = $this->getConnection()->fetchAssoc("SELECT GET_LOCK(?,?) AS getLock", array('locker_'.$lockName, $lockTime));

        return $result['getLock'];
    }

    public function release($lockName)
    {
        $result = $this->getConnection()->fetchAssoc("SELECT RELEASE_LOCK(?) AS releaseLock", array('locker_'.$lockName));

        return $result['releaseLock'];
    }

    protected function getConnection()
    {
        return $this->biz['db'];
    }
}
