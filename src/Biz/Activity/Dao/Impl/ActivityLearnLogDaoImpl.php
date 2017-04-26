<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ActivityLearnLogDao;
use Biz\Task\Dao\TaskResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ActivityLearnLogDaoImpl extends GeneralDaoImpl implements ActivityLearnLogDao
{
    protected $table = 'activity_learn_log';

    public function create($fields)
    {
        try {
            $month = date('m', time());
            if ($month % 2 !== 0) {
                return parent::create($fields);
            }

            $this->biz['db']->beginTransaction();

            $subfix = date('Y_m', strtotime('-2 month'));
            $sql = "SHOW tables LIKE '{$this->table()}_{$subfix}'";
            $tables = $this->db()->fetchAll($sql, array());
            if (empty($tables)) {
                $sql = "CREATE TABLE {$this->table()}_{$subfix} SELECT * FROM {$this->table()}";
                $this->db()->executeUpdate($sql);
                $sql = "DELETE FROM {$this->table()}";
                $this->db()->executeUpdate($sql);
            }

            $created = parent::create($fields);

            $this->biz['db']->commit();

            return $created;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    /**
     * @deprecated
     * @see TaskResultDao#getWatchTimeByActivityIdAndUserId
     *
     * @param $activityId
     * @param $userId
     *
     * @return int
     */
    public function sumWatchTimeByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT sum(learnedTime) FROM {$this->table()} WHERE activityId = ? and userId = ? and `event` = 'watching' ";

        return $this->db()->fetchColumn($sql, array($activityId, $userId)) ?: 0;
    }

    public function findRecentByActivityIdAndUserIdAndEvent($activityId, $userId, $event)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE activityId = ? and userId = ? and event = ?";

        return $this->db()->fetchAll($sql, array($activityId, $userId, $event)) ?: array();
    }

    /**
     * @deprecated
     *
     * @param $courseId
     * @param $userId
     *
     * @return int
     */
    public function countLearnedDaysByCourseIdAndUserId($courseId, $userId)
    {
        $sql = "SELECT count(distinct (from_unixtime(createdTime, '%Y-%m-%d')))
                FROM {$this->table()}
                WHERE userId = ? AND activityId IN (
                    SELECT id FROM activity WHERE fromCourseId = ?
                    )";

        return $this->db()->fetchColumn($sql, array($userId, $courseId)) ?: 0;
    }

    public function deleteByActivityId($activityId)
    {
        return $this->db()->delete($this->table(), array('activityId' => $activityId));
    }

    public function getLastestByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE activityId = ? AND userId = ? ORDER BY createdTime DESC";

        return $this->db()->fetchAssoc($sql, array($activityId, $userId));
    }

    public function declares()
    {
        return array(
            'orderbys' => array(
                'createdTime',
            ),
            'serializes' => array(
                'data' => 'json',
            ),
            'conditions' => array(
                'activityId = :activityId',
                'event_EQ = :event',
                'event_NEQ <> :event',
                'userId = :userId',
            ),
        );
    }
}
