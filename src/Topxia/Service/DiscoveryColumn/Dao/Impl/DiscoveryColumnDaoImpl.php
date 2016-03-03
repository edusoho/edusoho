<?php
namespace Topxia\Service\DiscoveryColumn\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\DiscoveryColumn\Dao\DiscoveryColumnDao;

class DiscoveryColumnDaoImpl extends BaseDao implements DiscoveryColumnDao
{
    protected $table = 'discovery_column';

    public function getDiscoveryColumn($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function updateDiscoveryColumn($id, $fields)
    {
        $fields['updateTime'] = time();
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getDiscoveryColumn($id);
    }

    public function addDiscoveryColumn($DiscoveryColumn)
    {
        $affected = $this->getConnection()->insert($this->table, $DiscoveryColumn);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert DiscoveryColumn error.');
        }

        return $this->getDiscoveryColumn($this->getConnection()->lastInsertId());
    }

    public function findDiscoveryColumnByTitle($title)
    {
        $sql             = "SELECT * FROM {$this->table} where title = ?";
        $DiscoveryColumn = $this->getConnection()->fetchAll($sql, array($title));
        return $DiscoveryColumn;
    }

    public function deleteDiscoveryColumn($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getAllDiscoveryColumns()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY seq";
        return $this->getConnection()->fetchAll($sql);
    }
}
