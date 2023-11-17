<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\FileDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class FileDaoImpl extends AdvancedDaoImpl implements FileDao
{
    protected $table = 'file';

    public function declares()
    {
        return [
            'timestamps' => [
                'createdTime',
            ],
            'orderbys' => [
                'createdTime',
            ],
            'conditions' => [
                'groupId = :groupId',
            ],
        ];
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function find($start, $limit)
    {
        return $this->search(
            [],
            [
                'createdTime' => 'DESC',
            ],
            $start,
            $limit
        );
    }

    public function countAll()
    {
        return $this->count([]);
    }

    public function findByGroupId($groupId, $start, $limit)
    {
        return $this->search(
            [
                'groupId' => $groupId,
            ],
            [
                'createdTime' => 'DESC',
            ],
            $start,
            $limit
        );
    }

    public function countByGroupId($groupId)
    {
        return $this->count([
            'groupId' => $groupId,
        ]);
    }

    public function deleteByUri($uri)
    {
        return $this->db()->delete($this->table, ['uri' => $uri]);
    }

    public function findByUris(array $uris)
    {
        return $this->findInField('uri', $uris);
    }
}
