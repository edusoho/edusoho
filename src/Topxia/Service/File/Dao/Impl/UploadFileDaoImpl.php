<?php

namespace Topxia\Service\File\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\File\Dao\UploadFileDao;

class UploadFileDaoImpl extends BaseDao implements UploadFileDao
{
    protected $table = 'upload_files';

    private $serializeFields = array(
        'metas2'        => 'json',
        'convertParams' => 'json'
    );

    public function getFile($id)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $file = $this->getConnection()->fetchAssoc($sql, array($id));
        return $file ? $this->createSerializer()->unserialize($file, $this->serializeFields) : null;
    }

    public function getFileByHashId($hash)
    {
        $sql = "SELECT * FROM {$this->table} WHERE hashId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($hash)) ?: null;
    }

    public function getFileByGlobalId($globalId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE globalId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($globalId)) ?: null;
    }

    public function getFileByConvertHash($hash)
    {
        $sql = "SELECT * FROM {$this->table} WHERE convertHash = ?";
        return $this->getConnection()->fetchAssoc($sql, array($hash)) ?: null;
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

    public function findFilesByTypeAndId($targetType, $targetId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE targetType = ? AND targetId = ?";
        return $this->getConnection()->fetchAll($sql, array($targetType, $targetId)) ?: array();
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
        $this->filterStartLimit($start, $limit);
        $builder = $this->createSearchQueryBuilder($conditions)
                        ->select('*')
                        ->orderBy($orderBy[0], $orderBy[1])
                        ->setFirstResult($start)
                        ->setMaxResults($limit);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function searchFileCount($conditions)
    {
        $builder = $this->createSearchQueryBuilder($conditions)
                        ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function deleteFile($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function deleteByGlobalId($globalId)
    {
        return $this->getConnection()->delete($this->table, array('globalId' => $globalId));
    }

    public function addFile(array $file)
    {
        $file['createdTime'] = time();
        $affected            = $this->getConnection()->insert($this->table, $file);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Course File disk file error.');
        }

        return $this->getFile($this->getConnection()->lastInsertId());
    }

    public function updateFile($id, array $fields)
    {
        $fields['updatedTime'] = time();
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getFile($id);
    }

    public function waveUploadFile($id, $field, $diff)
    {
        $fields = array('usedCount');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";

        $this->clearCached();

        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function getFileByTargetType($targetType)
    {
        $sql = "SELECT * FROM {$this->table} WHERE targetType = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($targetType));
    }

    protected function createSearchQueryBuilder($conditions)
    {
        $conditions           = array_filter($conditions,function ($value){
            if($value === '0') {
              return true;
            }
            if(empty($value)) {
              return false;
            }
            return true;
        });

        $conditions['status'] = 'ok';
        if (isset($conditions['filename'])) {
            $conditions['filenameLike'] = "%{$conditions['filename']}%";
            unset($conditions['filename']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, $this->table)
                        ->andWhere('targetType = :targetType')
                        ->andWhere('globalId = :globalId')
                        ->andWhere('targetId = :targetId')
                        ->andWhere('status = :status')
                        ->andWhere('isPublic = :isPublic')
                        ->andWhere('targetId IN ( :targets )')
                        ->andWhere('type = :type')
                        ->andWhere('storage = :storage')
                        ->andWhere('filename LIKE :filenameLike')
                        ->andWhere('id IN ( :ids )')
                        ->andWhere('createdUserId IN ( :createdUserIds )');

        return $builder;
    }
}
