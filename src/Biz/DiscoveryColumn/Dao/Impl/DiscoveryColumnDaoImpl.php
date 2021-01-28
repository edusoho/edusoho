<?php

namespace Biz\DiscoveryColumn\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Biz\DiscoveryColumn\Dao\DiscoveryColumnDao;

class DiscoveryColumnDaoImpl extends GeneralDaoImpl implements DiscoveryColumnDao
{
    protected $table = 'discovery_column';

    public function findByTitle($title)
    {
        return $this->findByFields(array('title' => $title));
    }

    public function findAllOrderBySeq()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY seq";

        return $this->db()->fetchAll($sql);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updateTime'),
            'orderbys' => array('seq'),
            'conditions' => array(
                'title = :title',
            ),
        );
    }
}
