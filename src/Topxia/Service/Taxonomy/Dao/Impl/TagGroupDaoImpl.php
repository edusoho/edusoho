<?php

namespace Topxia\Service\Taxonomy\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Taxonomy\Dao\TagGroupDao;

class TagGroupDaoImpl extends BaseDao implements TagGroupDao
{
    protected $table = 'tag_group';

    public function get($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function create($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert tag error.');
        }

        $this->clearCached();
        return $this->get($this->getConnection()->lastInsertId());
    }

    public function delete($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function update($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->get($id);

    }
}
