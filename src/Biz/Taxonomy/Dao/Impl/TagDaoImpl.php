<?php
/**
 * Created by PhpStorm.
 * User: Simon
 * Date: 12/12/2016
 * Time: 18:09.
 */

namespace Biz\Taxonomy\Dao\Impl;

use Biz\Taxonomy\Dao\TagDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class TagDaoImpl extends GeneralDaoImpl implements TagDao
{
    protected $table = 'tag';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'name = :name',
                'name LIKE :likeName',
                'orgId = :orgId',
                'orgCode = :orgCode',
                'orgCode LIKE :likeOrgCode',
            ),
        );
    }

    public function findByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE id IN ({$marks});";

        return $this->db()->fetchAll($sql, $ids);
    }

    public function findByNames(array $names)
    {
        if (empty($names)) {
            return array();
        }

        $marks = str_repeat('?,', count($names) - 1).'?';
        $sql = "SELECT * FROM {$this->table()} WHERE name IN ({$marks});";

        return $this->db()->fetchAll($sql, $names);
    }

    public function findAll($start, $limit)
    {
        $sql = "SELECT * FROM {$this->table()} ORDER BY createdTime DESC";
        $sql = $this->sql($sql, array(), $start, $limit);

        return $this->db()->fetchAll($sql, array());
    }

    public function getByName($name)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE name = ? LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($name));
    }

    public function findByLikeName($name)
    {
        $name = "%{$name}%";
        $sql = "SELECT * FROM {$this->table()} WHERE name LIKE ?";

        return $this->db()->fetchAll($sql, array($name)) ?: array();
    }

    public function getAllCount()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table()} ";

        return $this->db()->fetchColumn($sql, array());
    }
}
