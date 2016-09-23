<?php
namespace Permission\Service\Role\Dao\Impl;

use Permission\Service\Role\Dao\RoleDao;
use Topxia\Service\Common\BaseDao;

class RoleDaoImpl extends BaseDao implements RoleDao
{
    protected $table         = 'role';
    private $serializeFields = array(
        'data' => 'json'
    );
    public function getRole($id)
    {
        $self = $this;
        return $this->fetchCached("id:{$id}", $id, function ($id) use ($self) {
            $sql  = "SELECT * FROM {$self->getTable()} WHERE id = ? LIMIT 1";
            $role = $self->getConnection()->fetchAssoc($sql, array($id));
            return $role ? $self->createSerializer()->unserialize($role, $self->getSerializeFields()) : null;
        });
    }

    public function getRoleByCode($code)
    {
        $self = $this;
        return $this->fetchCached("code:{$code}", $code, function ($code) use ($self) {
            $sql  = "SELECT * FROM {$self->getTable()} WHERE code = ? LIMIT 1";
            $role = $self->getConnection()->fetchAssoc($sql, array($code));
            return $role ? $self->createSerializer()->unserialize($role, $self->getSerializeFields()) : null;
        });
    }

    public function findRolesByCodes($codes)
    {
        if (empty($codes)) {
            return array();
        }

        $marks = str_repeat('?,', count($codes) - 1).'?';
        $sql   = "SELECT * FROM {$this->getTable()} WHERE code IN ({$marks});";
        $roles = $this->getConnection()->fetchAll($sql, $codes);
        return $roles ? $this->createSerializer()->unserializes($roles, $this->getSerializeFields()) : array();
    }

    public function getRoleByName($name)
    {
        $sql  = "SELECT * FROM {$this->table} WHERE name = ? LIMIT 1";
        $role = $this->getConnection()->fetchAssoc($sql, array($name));
        return $role ? $this->createSerializer()->unserialize($role, $this->getSerializeFields()) : null;
    }

    public function createRole($role)
    {
        $this->createSerializer()->serialize($role, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $role);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert role error.');
        }

        $this->clearCached();
        return $this->getRole($this->getConnection()->lastInsertId());
    }

    public function updateRole($id, array $fields)
    {
        $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getRole($id);
    }

    public function deleteRole($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    public function searchRoles($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);

        $roles = $builder->execute()->fetchAll() ?: array();
        return $this->createSerializer()->unserializes($roles, $this->serializeFields);
    }

    public function searchRolesCount($conditions)
    {
        $builder = $this->createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
    }

    protected function createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere("name = :name")
            ->andWhere("name like :nameLike")
            ->andWhere("code = :code")
            ->andWhere('createdUserId = :createdUserId');
        return $builder;
    }

    protected function getRoleDao()
    {
        return $this->createDao('System.RoleDao');
    }

    public function getSerializeFields()
    {
        return $this->serializeFields;
    }
}
