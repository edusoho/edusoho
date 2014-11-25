<?php

namespace Custom\Service\Sign\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\Sign\Dao\SignCardDao;

class SignCardDaoImpl extends BaseDao implements SignCardDao
{
    protected $table = 'sign_card';

    public function addSignCard($signCard)
    {
        $affected = $this->getConnection()->insert($this->table, $signCard);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert sign_card Statistics error.');
        }
        return $this->getSignCard($this->getConnection()->lastInsertId());
    }

    public function getSignCard($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getSignCardByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($userId)) ? : null;
    }

    public function updateSignCard($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getSignCard($id);
    }

    public function waveCrad($id, $value)
    {
        $sql = "UPDATE {$this->table} SET cardNum = cardNum - ? WHERE id = ? LIMIT 1";
        
        return $this->getConnection()->executeQuery($sql, array($value, $id));
    }

}
