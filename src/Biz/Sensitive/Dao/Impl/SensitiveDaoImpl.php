<?php
namespace Biz\Sensitive\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\Sensitive\Dao\SensitiveDao;

class SensitiveDaoImpl extends GeneralDaoImpl implements SensitiveDao
{
    protected $table = 'keyword';

    public function declares()
    {
        return array();
    }

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
        $sql = "SELECT * FROM {$this->table} where state = ? ORDER BY createdTime DESC";
        return $this->db()->fetchAll($sql, array($state));
    }


    protected function _createQueryBuilder($conditions)
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
            } else

            if ($conditions['searchKeyWord'] == 'name') {
                $conditions['name'] = "%{$conditions['keyword']}%";
            }
        }

        return $this->createDynamicQueryBuilder($conditions)
                    ->from($this->table, 'keyword')
                    ->andWhere('id = :id')
                    ->andWhere('state = :state')
                    ->andWhere('UPPER(name) LIKE :name');
    }
}
