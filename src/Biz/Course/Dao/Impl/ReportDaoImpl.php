<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\ReportDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ReportDaoImpl extends GeneralDaoImpl implements ReportDao
{
    protected $courseMember = 'course_member';

    protected $course = 'course_v8';

    public function findCompleteCourseCountGroupByDate($courseId, $startTime, $endTime)
    {
        $sql = "SELECT FROM_UNIXTIME(lastLearnTime, '%Y-%m-%d' ) as date, COUNT(cm.id) AS count FROM {$this->course} AS cv8 JOIN {$this->courseMember} AS cm ON cv8.id = cm.courseId WHERE cv8.id = ? AND cm.learnedCompulsoryTaskNum >= cv8.compulsoryTaskNum AND cm.lastLearnTime BETWEEN ? AND ? GROUP  BY date";

        return $this->db()->fetchAll($sql, array($courseId, $startTime, $endTime)) ?: array();
    }

    public function declares()
    {
        return array();
    }
}
