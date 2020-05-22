<?php

namespace Codeages\Biz\ItemBank\Item\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\Item\Dao\AttachmentDao;

class AttachmentDaoImpl extends AdvancedDaoImpl implements AttachmentDao
{
    protected $table = 'biz_item_attachment';

    public function getByGlobalId($globalId)
    {
        return $this->getByFields(['global_id' => $globalId]);
    }

    public function findByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->findByFields(['target_id' => $targetId, 'target_type' => $targetType]);
    }

    public function findByTargetIdsAndTargetType($targetIds, $targetType)
    {
        if (empty($targetIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($targetIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE target_id IN ({$marks}) AND target_type = ?;";

        return $this->db()->fetchAll($sql, array_merge($targetIds, [$targetType]));
    }

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'orderbys' => [
                'id',
                'created_time',
            ],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'target_id IN (:target_ids)',
                'target_type = :target_type',
            ],
        ];
    }
}
