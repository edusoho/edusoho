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
                'target_type = :targetType',
                'target_type IN ( :targetTypes )',
                'global_id = :globalId',
                'global_id IN ( :globalIds )',
                'global_id <> ( :existGlobalId )',
                'status NOT IN ( :excludeStatus )',
                'target_type <> :noTargetType',
                'target_type NOT IN (:noTargetTypes)',
                'convert_status = :convertStatus',
                'target_id = :targetId',
                'status = :status',
                'target_id IN ( :targets )',
                'file_type = :type',
                'file_type IN ( :types)',
                'file_name LIKE :filenameLike',
                'created_time >= :startDate',
                'created_time < :endDate',
                'created_user_id IN ( :createdUserIds )',
                'created_user_id = :createdUserId',
                'audio_convert_status = :audioConvertStatus',
                'audio_convert_status IN ( :inAudioConvertStatus )',
            ],
        ];
    }
}
