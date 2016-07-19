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
            $sql   = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $block = $that->getConnection()->fetchAssoc($sql, array($id));

            return $block ? $that->createSerializer()->unserialize($block, $that->serializeFields) : null;
        }

        );
    }

    public function deleteBlockTemplate($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();

        return $result;
    }

    public function updateBlockTemplate($id, array $fields)
    {
        $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();

        return $this->getBlockTemplate($id);
    }

    public function getBlockTemplateByCode($code)
    {
        $that = $this;

        return $this->fetchCached("code:{$code}", $code, function ($code) use ($that) {
            $sql           = "SELECT * FROM {$that->getTable()} WHERE code = ? LIMIT 1";
            $blockTemplate = $that->getConnection()->fetchAssoc($sql, array($code));

            return $blockTemplate ? $that->createSerializer()->unserialize($blockTemplate, $that->serializeFields) : null;
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

    public function addBlockTemplate($blockTemplate)
    {
        $this->createSerializer()->serialize($blockTemplate, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $blockTemplate);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert blockTemplate error.');
        }

        return $this->getBlockTemplate($this->getConnection()->lastInsertId());
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'block_template')
            ->andWhere('id = :id')
            ->andWhere('category = :category')
            ->andWhere('code IN ( :codes )')
            ->andWhere('title LIKE :title');

        return $builder;
    }
}
