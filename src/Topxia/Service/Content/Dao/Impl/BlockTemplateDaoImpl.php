<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\BlockTemplateDao;

class BlockTemplateDaoImpl extends BaseDao implements BlockTemplateDao
{
    protected $table = 'block_template';

    public $serializeFields = array(
        'meta' => 'json',
        'data' => 'json'
    );

    public function getBlockTemplate($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $block = $that->getConnection()->fetchAssoc($sql, array($id));
            return $block ? $that->createSerializer()->unserialize($block, $that->serializeFields) : null;
        }

        );
    }

    public function searchBlockTemplates($conditions, $orderBy, $start, $limit)
    {
        if (!isset($orderBy) || empty($orderBy)) {
            $orderBy = array('updateTime', 'DESC');
        }
        
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->addOrderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchBlockTemplateCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function addBlockTemplate($content)
    {
        $affected = $this->getConnection()->insert($this->table, $content);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert content error.');
        }

        return $this->getConnection()->lastInsertId();
    }

    protected function _createSearchQueryBuilder($conditions)
    {

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'block_template')
            ->andWhere('id = :id')
            ->andWhere('category = :category');

        return $builder;
    }
}
