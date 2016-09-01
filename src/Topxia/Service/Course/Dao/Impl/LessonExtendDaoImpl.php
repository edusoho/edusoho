<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonExtendDao;

class LessonExtendDaoImpl extends BaseDao implements LessonExtendDao
{
    protected $table = 'course_lesson_extend';

    public function getLesson($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE id = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: array();
        }

        );
    }

    public function addLesson($lesson)
    {
        if (!isset($lesson['id'])) {
            throw $this->createDaoException('Id field not found.');
        }

        $lesson = $this->filterFields($lesson);
        if (empty($lesson)) {
            return array();
        }

        $affected = $this->getConnection()->insert($this->table, $lesson);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lesson error.');
        }

        return $this->getLesson($lesson['id']);
    }

    public function updateLesson($id, $fields)
    {
        $fields = $this->filterFields($fields);
        if (empty($fields)) {
            return array();
        }

        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getLesson($id);
    }

    public function deleteLessonsByCourseId($courseId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE courseId = ?";
        $result = $this->getConnection()->executeUpdate($sql, array($courseId));
        $this->clearCached();
        return $result;
    }

    public function deleteLesson($id)
    {
        $result = $this->getConnection()->delete($this->table, array('id' => $id));
        $this->clearCached();
        return $result;
    }

    protected function filterFields($lesson)
    {
        $tableColumn = $this->getTableColumns();
        $lesson      = ArrayToolkit::parts($lesson, $tableColumn);

        if (!empty($lesson['doTimes'])) {
            $lesson['redoInterval'] = 0;
        }

        return $lesson;
    }

    protected function getTableColumns()
    {
        $sql    = "SHOW COLUMNS FROM {$this->table}";
        $result = $this->getConnection()->fetchAll($sql);
        return ArrayToolkit::column($result, 'Field');
    }
}
