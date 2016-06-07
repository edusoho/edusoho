<?php
namespace Classroom\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Classroom\Service\Classroom\Dao\ClassroomDao;

class ClassroomDaoImpl extends BaseDao implements ClassroomDao
{
    protected $table = 'classroom';

    private $serializeFields = array(
        'assistantIds' => 'json',
        'teacherIds'   => 'json',
        'service'      => 'json'
    );

    public function getClassroom($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql       = "SELECT * FROM {$that->getTable()} where id=? LIMIT 1";
            $classroom = $that->getConnection()->fetchAssoc($sql, array($id));

            return $classroom ? $that->createSerializer()->unserialize($classroom, $that->getSerializeFields()) : null;
        }

        );
    }

    public function searchClassrooms($conditions, $orderBy, $start, $limit)
    {
        if (isset($conditions['classroomIds']) && empty($conditions['classroomIds'])) {
            return array();
        }

        $this->filterStartLimit($start, $limit);
        $orderBy = $this->checkOrderBy($orderBy, array('createdTime', 'recommendedSeq', 'studentNum'));

        $builder = $this->_createClassroomSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy($orderBy[0], $orderBy[1]);

        $classrooms = $builder->execute()->fetchAll();

        return $classrooms ? $this->createSerializer()->unserializes($classrooms, $this->serializeFields) : array();
    }

    public function findClassroomsByIds(array $ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks = str_repeat('?,', count($ids) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";

        $classrooms = $this->getConnection()->fetchAll($sql, $ids);

        return $classrooms ? $this->createSerializer()->unserializes($classrooms, $this->serializeFields) : array();
    }

    public function searchClassroomsCount($conditions)
    {
        if (isset($conditions['classroomIds']) && empty($conditions['classroomIds'])) {
            return 0;
        }

        $builder = $this->_createClassroomSearchBuilder($conditions)
            ->select('count(id)');

        return $builder->execute()->fetchColumn(0);
    }

    private function _createClassroomSearchBuilder($conditions)
    {
        if (isset($conditions['title'])) {
            $conditions['title'] = "%{$conditions['title']}%";
        }

        if (isset($conditions['likeOrgCode'])) {
            $conditions['likeOrgCode'] .= "%";
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('status = :status')
            ->andWhere('title like :title')
            ->andWhere('price > :price_GT')
            ->andWhere('price = :price')
            ->andWhere('private = :private')
            ->andWhere('categoryId IN (:categoryIds)')
            ->andWhere('categoryId =:categoryId')
            ->andWhere('id IN (:classroomIds)')
            ->andWhere('recommended = :recommended')
            ->andWhere('showable = :showable')
            ->andWhere('buyable = :buyable')
            ->andWhere('vipLevelId >= :vipLevelIdGreaterThan')
            ->andWhere('vipLevelId = :vipLevelId')
            ->andWhere('vipLevelId IN ( :vipLevelIds )')
            ->andWhere('orgCode = :orgCode')
            ->andWhere('orgCode LIKE :likeOrgCode');

        return $builder;
    }

    public function addClassroom($classroom)
    {
        $classroom = $this->createSerializer()->serialize($classroom, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $classroom);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Classroom error.');
        }

        return $this->getClassroom($this->getConnection()->lastInsertId());
    }

    public function findClassroomByTitle($title)
    {
        $sql = "SELECT * FROM {$this->table} where title=? LIMIT 1";

        $classroom = $this->getConnection()->fetchAssoc($sql, array($title));

        return $classroom ? $this->createSerializer()->unserialize($classroom, $this->serializeFields) : array();
    }

    public function findClassroomsByLikeTitle($title)
    {
        if (empty($title)) {
            return array();
        }

        $sql = "SELECT * FROM {$this->table} WHERE `title` LIKE ?; ";

        return $this->getConnection()->fetchAll($sql, array('%'.$title.'%'));
    }

    public function updateClassroom($id, $fields)
    {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        $this->clearCached();

        return $this->getClassroom($id);
    }

    public function waveClassroom($id, $field, $diff)
    {
        $fields = array('hitNum', 'auditorNum', 'studentNum', 'courseNum', 'lessonNum', 'threadNum', 'postNum', 'noteNum');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";

        $this->clearCached();

        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function deleteClassroom($id)
    {
        $this->clearCached();

        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getSerializeFields()
    {
        return $this->serializeFields;
    }
}
