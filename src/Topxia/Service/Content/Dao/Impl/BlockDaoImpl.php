<?php

namespace Topxia\Service\Content\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Content\Dao\BlockDao;

class BlockDaoImpl extends BaseDao implements BlockDao
{
    protected $table = 'block';

    public $serializeFields = array(
        'meta' => 'json',
        'data' => 'json'
    );

    public function getBlock($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $block = $that->getConnection()->fetchAssoc($sql, array($id));
            return $block ? $that->createSerializer()->unserialize($block, $that->serializeFields) : null;
        }

        );
    }

    public function getBlockByTemplateIdAndOrgId($blockTemplateId,$orgId=0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE blockTemplateId = '{$blockTemplateId}' AND orgId =  '{$orgId}' ";
        $block = $this->getConnection()->fetchAssoc($sql, array($blockTemplateId,$orgId));

        return $block ? $this->createSerializer()->unserialize($block, $this->serializeFields) : null;
    }

    public function getBlockByTemplateId($blockTemplateId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE blockTemplateId = '{$blockTemplateId}'";
        $block = $this->getConnection()->fetchAssoc($sql, array($blockTemplateId));

        return $block ? $this->createSerializer()->unserialize($block, $this->serializeFields) : null;
    }

    protected function isSortField($condition)
    {
        if (isset($condition['category']) && $condition['category'] == 'lastest') {
            return true;
        }

        return false;
    }

    public function addBlock($block)
    {
        if (isset($block['blockId']))
         {
            unset($block['blockId']);
        }
        $this->createSerializer()->serialize($block, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $block);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert block error.');
        }

        return $this->getBlock($this->getConnection()->lastInsertId());
    }

    public function deleteBlock($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function getBlockByCode($code)
    {
        $that = $this;
        return $this->fetchCached("code:{$code}", $code, function ($code) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE code = '{$code}'";
            $block = $that->getConnection()->fetchAssoc($sql, array($code));
            return $block ? $that->createSerializer()->unserialize($block, $that->serializeFields) : null;
        });
    }

    public function getBlockByCodeAndOrgId($code,$orgId=0)
    {
        $that = $this;
        return $this->fetchCached("code:{$code}:orgId:{$orgId}", $code, $orgId, function ($code, $orgId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE code = '{$code}' AND orgId =  '{$orgId}' ";
            $block = $that->getConnection()->fetchAssoc($sql, array($code,$orgId));
            return $block ? $that->createSerializer()->unserialize($block, $that->serializeFields) : null;
        });
    }
    
    public function updateBlock($id, array $fields)
    {
        if (isset($fields['blockId']))
         {
            unset($fields['blockId']);
        }
        $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getBlock($id);
    }
}
