<?php

namespace Biz\File\Dao\Impl;

use Biz\File\Dao\UploadFileDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UploadFileDaoImpl extends GeneralDaoImpl implements UploadFileDao
{
    protected $table = 'upload_files';

    public function create($file)
    {
        if (!isset($file['id'])) {
            return [];
        }

        parent::create($file);

        return $this->get($file['id']);
    }

    public function getByHashId($hash)
    {
        return $this->getByFields([
            'hashId' => $hash,
        ]);
    }

    public function getByGlobalId($globalId)
    {
        return $this->getByFields([
            'globalId' => $globalId,
        ]);
    }

    public function getByConvertHash($hash)
    {
        return $this->getByFields([
            'convertHash' => $hash,
        ]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        if (empty($targetIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($targetIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE targetType = ? AND targetId IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge([$targetType], $targetIds)) ?: [];
    }

    public function findCloudFilesByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE id IN ({$marks}) and storage='cloud' and globalId!='0';";

        return $this->db()->fetchAll($sql, $ids);
    }

    public function countByEtag($etag)
    {
        if (empty($etag)) {
            return 0;
        }

        return $this->count(['etag' => $etag]);
    }

    public function deleteByGlobalId($globalId)
    {
        $result = $this->db()->delete($this->table, ['globalId' => $globalId]);

        return $result;
    }

    public function waveUsedCount($id, $num)
    {
        return $this->wave([$id], [
            'usedCount' => $num,
        ]);
    }

    public function getByTargetType($targetType)
    {
        return $this->getByFields([
            'targetType' => $targetType,
        ]);
    }

    public function findHeadLeaderFiles()
    {
        $sql = "SELECT * FROM {$this->table()} WHERE targetType = 'headLeader'";

        return $this->db()->fetchAll($sql, []);
    }

    public function declares()
    {
        return [
            'conditions' => [
                'etag = :etag',
                'targetType = :targetType',
                'targetType IN ( :targetTypes )',
                'useType IN ( :useTypes)',
                'useType LIKE :useTypeLike',
                'globalId = :globalId',
                'globalId IN ( :globalIds )',
                'globalId <> ( :existGlobalId )',
                'targetType <> :noTargetType',
                'targetType NOT IN (:noTargetTypes)',
                'convertStatus = :convertStatus',
                'targetId = :targetId',
                'status = :status',
                'isPublic = :isPublic',
                'targetId IN ( :targets )',
                'type = :type',
                'type IN ( :types)',
                'storage = :storage',
                'filename LIKE :filenameLike',
                'id IN ( :ids )',
                'createdTime >= :startDate',
                'createdTime < :endDate',
                'usedCount >= :startCount',
                'usedCount < :endCount',
                'createdUserId IN ( :createdUserIds )',
                'createdUserId = :createdUserId',
                'id IN ( :idsOr )',
                'audioConvertStatus = :audioConvertStatus',
                'audioConvertStatus IN ( :inAudioConvertStatus )',
                /*S2B2C-CUSTOM*/
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
                /*END*/
            ],
            'serializes' => [
                'metas2' => 'json',
                'metas' => 'json',
                'convertParams' => 'json',
            ],
            'orderbys' => [
                'createdTime',
                'id',
            ],
        ];
    }

    protected function createQueryBuilder($conditions)
    {
        $conditions['status'] = 'ok';

        if (isset($conditions['filename'])) {
            $conditions['filenameLike'] = "%{$conditions['filename']}%";
            unset($conditions['filename']);
        }

        if (isset($conditions['useType'])) {
            $conditions['useTypeLike'] = "%{$conditions['useType']}%";
            unset($conditions['useType']);
        }

        return parent::createQueryBuilder($conditions);
    }
}
