<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\NavigationDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class NavigationDaoImpl extends GeneralDaoImpl implements NavigationDao
{
    protected $table = 'navigation';

    public function countByType($type)
    {
        return $this->count(array(
            'type' => $type,
        ));
    }

    public function deleteByParentId($parentId)
    {
        return $this->db()->delete($this->table, array('parentId' => $parentId));
    }

    public function countAll()
    {
        return $this->count(array());
    }

    public function findByType($type, $start, $limit)
    {
        return $this->search(
            array(
                'type' => $type,
            ),
            array(
                'sequence' => 'ASC',
            ),
            $start,
            $limit
        );
    }

    public function find($start, $limit)
    {
        return $this->search(
            array(),
            array(
                'sequence' => 'ASC',
            ),
            $start,
            $limit
        );
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'name = :name',
                'type = :type',
                'isOpen = :isOpen',
                'isNewWin =:isNewWin',
                'orgId = :orgId',
                'orgCode = :orgCode',
                'orgCode LIKE :likeOrgCode',
            ),
            'orderbys' => array(
                'sequence',
            ),
        );
    }

    protected function createQueryBuilder($conditions)
    {
        if (empty($conditions['orgId'])) {
            unset($conditions['orgId']);
        }

        if (isset($conditions['likeOrgCode'])) {
            unset($conditions['orgCode']);
        }

        return parent::createQueryBuilder($conditions);
    }
}
