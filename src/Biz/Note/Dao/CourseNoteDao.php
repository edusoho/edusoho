<?php
namespace Biz\Note\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseNoteDao extends GeneralDaoInterface
{
    public function getByUserIdAndTaskId($userId, $taskId);

    public function findByUserIdAndStatus($userId, $status);

    public function findByUserIdAndCourseId($userId, $courseId);

    public function countByUserIdAndCourseId($userId, $courseId);

}
