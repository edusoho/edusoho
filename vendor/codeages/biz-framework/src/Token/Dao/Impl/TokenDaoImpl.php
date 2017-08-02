<?php

namespace Codeages\Biz\Framework\Token\Dao\Impl;

use Codeages\Biz\Framework\Token\Dao\TokenDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TokenDaoImpl extends GeneralDaoImpl implements TokenDao
{
    protected $table = 'biz_token';

    public function getByKey($key)
    {
        return $this->getByFields(array('_key' => $key));
    }

    public function deleteExpired($timestamp)
    {
        $sql = "DELETE FROM {$this->table()} WHERE expired_time > 0 AND expired_time < ?";
        $this->db()->executeQuery($sql, array(intval($timestamp)));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time'),
            'serializes' => array('data' => 'php'),
            'cache' => false, // 该Dao禁用Cache
            'conditions' => array(
            ),
        );
    }
}
