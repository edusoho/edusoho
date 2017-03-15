<?php

namespace Biz\Sensitive\Dao\Impl;

use Biz\Sensitive\Dao\SensitiveDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class SensitiveDaoImpl extends GeneralDaoImpl implements SensitiveDao
{
    protected $table = 'keyword';

    public function getByName($name)
    {
        return $this->getByFields(array('name' => $name));
    }

    public function findAllKeywords()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, array());
    }

    public function findByState($state)
    {
        return $this->findInField('state', array($state));
    }

    public function declares()
    {
        $declares['orderbys'] = array(
            'createdTime', 'id',
        );

        $declares['conditions'] = array(
            'id = :id',
            'state = :state',
            'UPPER(name) LIKE :name',
        );

        return $declares;
    }

    protected function createQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($v) {
            if ($v === 0) {
                return true;
            }

            if (empty($v)) {
                return false;
            }

            return true;
        }
        );

        if (isset($conditions['keyword'])) {
            if ($conditions['searchKeyWord'] == 'id') {
                $conditions['id'] = $conditions['keyword'];
            } elseif ($conditions['searchKeyWord'] == 'name') {
                $conditions['name'] = "%{$conditions['keyword']}%";
            }
        }

        return parent::createQueryBuilder($conditions);
    }
}
