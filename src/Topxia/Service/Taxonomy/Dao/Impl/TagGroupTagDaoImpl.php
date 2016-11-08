<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagGroupTagDao;

class TagGroupTagDaoImpl extends BaseDao implements TagGroupTagDao
{
    protected $table = 'tag_group_tag';

    public function findTagsByGroupId($groupId)
    {
        $that = $this;

        return $this->fetchCached("groupId:{$groupId}", $groupId, function ($groupId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE groupId = ? ORDER BY weight ASC";
            return $that->getConnection()->fetchAll($sql, array($groupId)) ?: array();
        }

        );
    }

        public function search($conditions, $order, $start, $limit)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table)
            ->andWhere('name = :name');

        return $builder;
    }

}
