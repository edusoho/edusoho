<?php
namespace Custom\Service\Course\Dao\Impl;
use Custom\Service\Course\Dao\CourseScoreSettingDao;
use Topxia\Service\Common\BaseDao;

class CourseScoreSettingDaoImpl extends BaseDao implements CourseScoreSettingDao {
	protected $table = "course_score_setting";

	public function getScoreSettingByCourseId($courseId) {
		$sql = "SELECT * FROM {$this->table} WHERE courseId = ?";
		return $this->getConnection()->fetchAssoc($sql, array(
			$courseId,
		)) ?: null;
	}

	public function addScoreSetting($scoreSetting) {
		$affected = $this->getConnection()->insert($this->table, $scoreSetting);
		if ($affected <= 0) {
			throw $this->createDaoException('Insert courseScoreSetting error.');
		}
		return $this->getScoreSettingByCourseId($scoreSetting['courseId']);
	}

	public function updateScoreSetting($courseId, $fields) {
		$this->getConnection()->update($this->table, $fields, array(
			'courseId' => $courseId,
		));
		return $this->getScoreSettingByCourseId($courseId);
	}

	public function findScoreSettingsByCourseIds($courseIds)
	{
		$marks = str_repeat('?,', count($courseIds) - 1) . '?';

        $sql = "SELECT * FROM {$this->table} WHERE courseId IN ({$marks})";
        $scoreSettings =  $this->getConnection()->fetchAll($sql, $courseIds);
        return $scoreSettings;
	}

	protected function _createSearchQueryBuilder($courseIds)
    {
        $builder = $this->createDynamicQueryBuilder($courseIds)
            ->from($this->table, $this->table)     
            ->andWhere('courseId IN ( :courseIds )');

        return $builder;
    }
}
