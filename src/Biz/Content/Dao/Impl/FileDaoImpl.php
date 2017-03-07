<?php

namespace Biz\Content\Dao\Impl;

use Biz\Content\Dao\FileDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FileDaoImpl extends GeneralDaoImpl implements FileDao
{
    protected $table = 'file';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'createdTime',
            ),
            'conditions' => array(
                'groupId = :groupId',
            ),
        );
    }

    public function findByIds(array $ids)
    {
        return $this->findInField('id', $ids);
    }

    public function find($start, $limit)
    {
        return $this->search(
            array(),
            array(
                'createdTime' => 'DESC',
            ),
            $start,
            $limit
        );
    }

    public function countAll()
    {
        return $this->count(array());
    }

    public function findByGroupId($groupId, $start, $limit)
    {
        return $this->search(
            array(
                'groupId' => $groupId,
            ),
            array(
                'createdTime' => 'DESC',
            ),
            $start,
            $limit
        );
    }

    public function countByGroupId($groupId)
    {
        return $this->count(array(
            'groupId' => $groupId,
        ));
    }

    public function deleteByUri($uri)
    {
        return $this->db()->delete($this->table, array('uri' => $uri));
    }
}
