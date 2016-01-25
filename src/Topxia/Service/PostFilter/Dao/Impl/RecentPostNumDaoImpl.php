<?php

namespace Topxia\Service\PostFilter\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\PostFilter\Dao\RecentPostNumDao;

class RecentPostNumDaoImpl  extends BaseDao implements RecentPostNumDao
{
	protected $table = 'recent_post_num';

    public function getRecentPostNumByIpAndType($ip, $type)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE ip = ? and type = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($ip, $type)) ? : null;
    }

    public function getRecentPostNum($id)
    {
    	$sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function deleteRecentPostNum($id)
    {
    	return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function addRecentPostNum($fields)
    {
    	$affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert recent post num error.');
        }
        return $this->getRecentPostNum($this->getConnection()->lastInsertId());
    }

    public function waveRecentPostNum($id, $field, $diff)
    {
    	$fields = array('num');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $currentTime = time();
        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ?, updatedTime = ?  WHERE id = ? LIMIT 1";
        
        return $this->getConnection()->executeQuery($sql, array($diff, $currentTime, $id));
    }

}