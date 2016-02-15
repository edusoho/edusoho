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

    public function getAnalysisLessonMinTime($type)
    {
        $condition = $this->_filterTypeCondition($type);
        $sql = "SELECT `createdTime` FROM {$this->table} {$condition} ORDER BY `createdTime` ASC LIMIT 1;";
        return $this->getConnection()->fetchAssoc($sql) ? : null;
    }

    public function searchLessonView($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->orderBy($orderBy[0], $orderBy[1])
            ->setFirstResult($start)
            ->setMaxResults($limit);
    
        return $builder->execute()->fetchAll() ? : array(); 
    }

	public function searchLessonViewGroupByTime($startTime,$endTime,$conditions)
	{
        $params = array($startTime, $endTime);

        $conditionStr = "";

        if (array_key_exists("fileType", $conditions)) {
            $conditionStr .= " AND `fileType` = ? ";
            $params[] = $conditions['fileType'];
        }
        
        if (array_key_exists("fileStorage", $conditions)) {
            $conditionStr .= " AND `fileStorage` = ? ";
            $params[] = $conditions['fileStorage'];
        }

		$sql="SELECT count(`id`) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE  `createdTime`>=? AND `createdTime`<=? {$conditionStr} group by date_format(from_unixtime(`createdTime`),'%Y-%m-%d') order by date ASC ";

        return $this->getConnection()->fetchAll($sql, $params);
	}

    public function deleteLessonView($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'course_lesson_view')
            ->andWhere('fileType = :fileType')
            ->andWhere('fileStorage = :fileStorage')
            ->andWhere('courseId = :courseId')
            ->andWhere('createdTime >= :startTime')
            ->andWhere('createdTime <= :endTime');
        return $builder;
    }

    protected function _filterTypeCondition($type)
    {
        if (in_array($type, array('net','local','cloud'))) {
           return "WHERE `fileType` = '{$type}'";
        }

        return "";
    }
}