<?php
namespace Topxia\Service\Sale\Dao\Impl;

use Topxia\Service\Common\BaseDao;

use Topxia\Service\Sale\Dao\OffsaleDao;

class OffsaleDaoImpl extends BaseDao implements OffsaleDao
{
    protected $table = 'offsale';

    public function getOffsale($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }


    public function findOffsalesByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }


    public function addOffsale($offsale)
    {
        $affected = $this->getConnection()->insert($this->table, $offsale);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert  offsale error.');
        }
        return $this->getOffsale($this->getConnection()->lastInsertId());
    }

    public function updateOffsale($id, $offsale)
    {
        $this->getConnection()->update($this->table, $offsale, array('id' => $id));
        return $this->getOffsale($id);
    }

    public function deleteOffsale($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function batchGeOffsale($offset)
    {

    }

    public function getOffsaleByCode($code)
    {
        $sql = "SELECT * FROM {$this->table} WHERE promocode = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($code)) ? : null;
    }

   

}