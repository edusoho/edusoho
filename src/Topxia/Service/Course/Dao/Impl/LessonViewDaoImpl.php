<?php 

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonViewDao;

class LessonViewDaoImpl extends BaseDao implements LessonViewDao
{
    protected $table = 'course_lesson_view';

	public function getLessonView($id)
	{
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
	}

	public function addLessonView($lessonView)
	{
		$affected = $this->getConnection()->insert($this->table, $lessonView);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert LessonView error.');
        }
        return $this->getLessonView($this->getConnection()->lastInsertId());
	}

	public function searchLessonViewCount($conditions)
	{
	    $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');
        return $builder->execute()->fetchColumn(0);
	}

	public function searchLessonViewGroupByTime($startTime,$endTime,$conditions)
	{
		$sql="SELECT count(`id`) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->getTablename()}` WHERE  `createdTime`>={$startTime} and `createdTime`<={$endTime} group by date_format(from_unixtime(`createdTime`),'%Y-%m-%d') order by date ASC ";
        return $this->getConnection()->fetchAll($sql);
	}

    private function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_lesson_view')
            ->andWhere('fileType = :fileType')
            ->andWhere('fileStorage = :fileStorage')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime');
        return $builder;
    }
}