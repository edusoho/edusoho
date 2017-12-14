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
            return array();
        }

        parent::create($file);

        return $this->get($file['id']);
    }

    public function getByHashId($hash)
    {
        return $this->getByFields(array(
            'hashId' => $hash,
        ));
    }

    public function getByGlobalId($globalId)
    {
        return $this->getByFields(array(
            'globalId' => $globalId,
        ));
    }

    public function getByConvertHash($hash)
    {
        return $this->getByFields(array(
            'convertHash' => $hash,
        ));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        if (empty($targetIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($targetIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE targetType = ? AND targetId IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge(array($targetType), $targetIds)) ?: array();
    }

    public function findCloudFilesByIds($ids)
    {
        if (empty($ids)) {
            return array();
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

        return $this->count(array('etag' => $etag));
    }

    public function deleteByGlobalId($globalId)
    {
        $result = $this->db()->delete($this->table, array('globalId' => $globalId));

        return $result;
    }

    public function waveUsedCount($id, $num)
    {
        return $this->wave(array($id), array(
            'usedCount' => $num,
        ));
    }

    public function getByTargetType($targetType)
    {
        return $this->getByFields(array(
            'targetType' => $targetType,
        ));
    }

    public function findHeadLeaderFiles()
    {
        $sql = "SELECT * FROM {$this->table()} WHERE targetType = 'headLeader'";

        return $this->db()->fetchAll($sql, array());
    }

    public function declares()
    {
        return array(
            'conditions' => array(
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
            ),
            'serializes' => array(
                'metas2' => 'json',
                'metas' => 'json',
                'convertParams' => 'json',
            ),
            'orderbys' => array(
                'createdTime',
                'id',
            ),
        );
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
