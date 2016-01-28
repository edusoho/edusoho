<?php
namespace Topxia\Service\Group\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Group\Dao\GroupDao;

class GroupDaoImpl extends BaseDao implements GroupDao
{
    protected $table = 'groups';

    public $serializeFields = array(
        'tagIds' => 'json'
    );

    public function searchGroupsCount($conditions)
    {
        $builder = $this->_createGroupSearchBuilder($conditions)
                        ->select('count(id)');
        return $builder->execute()->fetchColumn(0);
    }

    public function waveGroup($id, $field, $diff)
    {
        $fields = array('postNum', 'threadNum', 'memberNum');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $sql    = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
        $result = $this->getConnection()->executeQuery($sql, array($diff, $id));
        $this->clearCached();
        return $result;
    }

    public function getGroup($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where id=? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function addGroup($group)
    {
        $group = $this->createSerializer()->serialize($group, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $group);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Group error.');
        }

        return $this->getGroup($this->getConnection()->lastInsertId());
    }

    public function updateGroup($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getGroup($id);
    }

    public function searchGroups($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createGroupSearchBuilder($conditions)
                        ->select('*')
                        ->setFirstResult($start)
                        ->setMaxResults($limit)
                        ->addOrderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function getGroupsByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';

        $that = $this;
        $keys = implode(',', $ids);
        return $this->fetchCached("ids:{$keys}", $marks, $ids, function ($marks, $ids) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id IN ({$marks});";

            return $that->getConnection()->fetchAll($sql, $ids);
        }

        );
    }

    public function getGroupByTitle($title)
    {
        $that = $this;

        return $this->fetchCached("title:{$title}", $title, function ($title) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE title=? ";

            return $that->getConnection()->fetchAll($sql, array($title)) ?: array();
        }

        );
    }

    protected function _createGroupSearchBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
                        ->from($this->table, $this->table)
                        ->andWhere('ownerId=:ownerId')
                        ->andWhere('status = :status')
                        ->andWhere('title like :title');

        return $builder;
    }
}
