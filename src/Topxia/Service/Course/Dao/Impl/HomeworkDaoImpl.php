<?php 

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\HomeworkDao;

class HomeworkDaoImpl extends BaseDao Implements HomeworkDao
{
	protected $table = 'homework';

	public function getHomework($id)
	{
		$sql = "SELECT * FROM {$this->table} Where id = ?";
		return $this->getConnection()->fetchAssoc($sql,array($id)) ? : null;
	}

    public function getHomeworkByCourseIdAndLessonId($courseId, $lessonId)
    {
        $sql = "SELECT * FROM {$this->table} Where courseId = ? And lessonId = ? Limit 1";
        return $this->getConnection()->fetchAssoc($sql, array($courseId, $lessonId)) ? : null;
    }

	public function getHomeworkResult($id)
	{

	}

	public function searchHomeworks($conditions, $sort, $start, $limit)
	{

	}

	public function addHomework($fields)
	{
		$affect = $this->getConnection()->insert($this->table,$fields);
		if ($affect <= 0) {
			throw $this->createDaoException('insert homework error!');
		}
		return $this->getHomework($this->getConnection()->lastInsertId());
	}

    public function updateHomework($id,$fields)
    {

    }

    public function deleteHomework($id)
    {

    }

    public function deleteHomeworksByCourseId($courseId)
    {

    }

    public function findHomeworkResultsByStatusAndCheckTeacherId($checkTeacherId, $status)
    {

    }

    public function findHomeworkResultsByCourseIdAndStatusAndCheckTeacherId($courseId,$checkTeacherId, $status)
    {

    }

    public function findHomeworkResultsByStatusAndStatusAndUserId($userId, $status)
    {

    }

    public function findAllHomeworksByCourseId($courseId)
    {

    }

}