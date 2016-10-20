<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileDao;

class UploadFileDaoImpl extends BaseDao implements UploadFileDao
{
    protected $table = 'upload_files';

    public $serializeFields = array(
        'metas2'        => 'json',
        'convertParams' => 'json'
    );

    public function getFile($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql  = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            $file = $that->getConnection()->fetchAssoc($sql, array($id));
            return $file ? $that->createSerializer()->unserialize($file, $that->serializeFields) : null;
        });
    }

    public function getFileByHashId($hash)
    {
        $that = $this;

        return $this->fetchCached("hashId:{$hash}", $hash, function ($hash) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE hashId = ?";
            return $that->getConnection()->fetchAssoc($sql, array($hash)) ?: null;
        });
    }

    public function getFileByGlobalId($globalId)
    {
        $that = $this;

        return $this->fetchCached("globalId:{$globalId}", $globalId, function ($globalId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE globalId = ?";
            return $that->getConnection()->fetchAssoc($sql, array($globalId)) ?: null;
        });
    }

    public function getFileByConvertHash($hash)
    {
        $that = $this;

        return $this->fetchCached("convertHash:{$hash}", $hash, function ($hash) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE convertHash = ?";
            return $that->getConnection()->fetchAssoc($sql, array($hash)) ?: null;
        });
    }

    public function findFilesByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findFilesByTargetTypeAndTargetIds($targetType, $targetIds)
    {
        if (empty($targetIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($targetIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE targetType = ? AND targetId IN ({$marks})";
        return $this->getConnection()->fetchAll($sql, array_merge(array($targetType), $targetIds)) ?: array();
    }

    public function findCloudFilesByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks}) and storage='cloud' and globalId!='0';";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findFilesCountByEtag($etag)
    {
        if (empty($etag)) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE etag = ? ";
        return $this->getConnection()->fetchColumn($sql, array($etag));
    }

    public function searchFiles($conditions, $orderBy, $start, $limit)
    {
        if (empty($orderBy)) {
            $orderBy = array('id', 'DESC');
        }

        if ($this->hasEmptyInCondition($conditions, array("globalIds", "targetTypes", "targets", "ids", "createdUserIds", "idsOr"))) {
            return array();
        }
        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('id', 'updatedTime', 'createdTime', 'ext', 'filename', 'fileSize'));

        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchFileCount($conditions)
    {
        if ($this->hasEmptyInCondition($conditions, array("globalIds", "targetTypes", "targets", "ids", "createdUserIds", "idsOr"))) {
            return 0;
        }
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function deleteFile($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function deleteByGlobalId($globalId)
    {
        $result = $this->getConnection()->delete($this->table, array('globalId' => $globalId));
        $this->clearCached();
        return $result;
    }

    public function addFile(array $file)
    {
        $file['createdTime'] = time();
        $affected            = $this->getConnection()->insert($this->table, $file);
        $this->clearCached();
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Course File disk file error.');
        }

        return $this->getFile($file['id']);
    }

    public function updateFile($id, array $fields)
    {
        $fields['updatedTime'] = time();
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getFile($id);
    }

    public function waveUploadFile($id, $field, $diff)
    {
        $fields = array('usedCount');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf($this->getKernel()->trans('%s字段不允许增减，只有%s才被允许增减'), $field, implode(',', $fields)));
        }

        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";

        $this->clearCached();

        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function getFileByTargetType($targetType)
    {
        $that = $this;

        return $this->fetchCached("targetType:{$targetType}", $targetType, function ($targetType) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE targetType = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($targetType));
        });
    }

    public function getHeadLeaderFiles()
    {
        $that = $this;

        return $this->fetchCached("targetType:headLeader", function () use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE targetType = 'headLeader'";
            return $that->getConnection()->fetchAll($sql, array());
        });
    }

    protected function createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions, function ($value) {
            if ($value === 0) {
                return true;
            }

            if (empty($value)) {
                return false;
            }

            return true;
        });
        $conditions['status'] = 'ok';

        if (isset($conditions['filename'])) {
            $conditions['filenameLike'] = "%{$conditions['filename']}%";
            unset($conditions['filename']);
        }

        if (isset($conditions['useType'])) {
            $conditions['useTypeLike'] = "%{$conditions['useType']}%";
            unset($conditions['useType']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('targetType = :targetType')
            ->andWhere('targetType IN ( :targetTypes )')
            ->andWhere('useType IN ( :useTypes)')
            ->andWhere('useType LIKE :useTypeLike')
            ->andWhere('globalId = :globalId')
            ->andWhere('globalId IN ( :globalIds )')
            ->andWhere('globalId <> ( :existGlobalId )')
            ->andWhere('targetType <> :noTargetType')
            ->andWhere('convertStatus = :convertStatus')
            ->andWhere('targetId = :targetId')
            ->andWhere('status = :status')
            ->andWhere('isPublic = :isPublic')
            ->andWhere('targetId IN ( :targets )')
            ->andWhere('type = :type')
            ->andWhere('storage = :storage')
            ->andWhere('filename LIKE :filenameLike')
            ->andWhere('id IN ( :ids )')
            ->andWhere('createdTime >= :startDate')
            ->andWhere('createdTime < :endDate')
            ->andWhere('usedCount >= :startCount')
            ->andWhere('usedCount < :endCount')
            ->andWhere('createdUserId IN ( :createdUserIds )')
            ->andWhere('createdUserId = :createdUserId')
            ->orWhere('id IN ( :idsOr )');

        return $builder;
    }
}
