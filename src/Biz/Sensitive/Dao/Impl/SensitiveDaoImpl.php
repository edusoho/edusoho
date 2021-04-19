<?php

namespace Biz\Sensitive\Dao\Impl;

use Biz\Sensitive\Dao\SensitiveDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class SensitiveDaoImpl extends AdvancedDaoImpl implements SensitiveDao
{
    protected $table = 'keyword';

    public function getByName($name)
    {
        return $this->getByFields(['name' => $name]);
    }

    public function findAllKeywords()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, []);
    }

    public function findByState($state)
    {
        return $this->findInField('state', [$state]);
    }

    public function declares()
    {
        return [
            'orderbys' => ['createdTime', 'id'],
            'timestamps' => ['createdTime'],
            'conditions' => [
                'id = :id',
                'state = :state',
                'UPPER(name) LIKE :name',
                'name IN (:names)',
            ],
        ];
    }

    protected function createQueryBuilder($conditions)
    {
        $conditions = array_filter(
            $conditions,
            function ($v) {
                if (0 === $v) {
                    return true;
                }

                if (empty($v)) {
                    return false;
                }

                return true;
            }
        );

        if (isset($conditions['keyword'])) {
            if ('id' === $conditions['searchKeyWord']) {
                $conditions['id'] = $conditions['keyword'];
            } elseif ('name' === $conditions['searchKeyWord']) {
                $conditions['name'] = "%{$conditions['keyword']}%";
            }
        }

        return parent::createQueryBuilder($conditions);
    }
}
