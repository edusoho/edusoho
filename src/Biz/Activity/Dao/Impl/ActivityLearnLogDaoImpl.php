<?php

namespace Biz\Activity\Dao\Impl;

use Biz\Activity\Dao\ActivityLearnLogDao;
use Biz\Lock;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ActivityLearnLogDaoImpl extends GeneralDaoImpl implements ActivityLearnLogDao
{
    protected $table = 'activity_learn_log';

    protected $lock;

    public function create($fields)
    {
        $month = date('m', time());
        if (0 !== $month % 2) {
            return parent::create($fields);
        }
        $lock = $this->getLock();
        try {
            $suffix = date('Y_m', strtotime('-2 month'));
            $lock->get('activity_learn_log', 10);
            if ($this->isTableExists($suffix)) {
                $this->archiveLogs($suffix);
            }
            $lock->release('activity_learn_log');

            return parent::create($fields);
        } catch (\Exception $e) {
            $lock->release('activity_learn_log');
            throw $e;
        }
    }

    private function isTableExists($subfix)
    {
        $sql = "SHOW tables LIKE '{$this->table()}_{$subfix}'";
        $tables = $this->db()->fetchAll($sql, array());

        return !empty($tables);
    }

    private function archiveLogs($subfix)
    {
        $sql = "RENAME TABLE {$this->table()} TO {$this->table()}_{$subfix}";
        $this->db()->execute($sql);
        $sql = "CREATE TABLE {$this->table()} LIKE {$this->table()}_{$subfix}";
        $this->db()->execute($sql);
    }

    public function getRecentFinishedLogByActivityIdAndUserId($activityId, $userId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE activityId = ? and userId = ? and event = 'finish'";

        return $this->db()->fetchAll($sql, array($activityId, $userId)) ?: array();
    }

    /**
     * @deprecated
     *
     * @todo
     * 对activity_learn_log分表后无法使用该方式统计用户对某一课程的累计学习天数，应使用临时表的方式解决：
     * 即：定时将activity_learn_log原始数据按照业务进行精简汇总保存到某临时表中，从临时表查询所需数据
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

    public function sumLearnTimeGroupByUserId($conditions)
    {
        $conditions['learnedTime_GE'] = 0;
        $conditions['learnedTime_LE'] = 24 * 60 * 60;
        $conditions['event_NEQ'] = 'watching';

        $builder = $this->createQueryBuilder($conditions)
            ->select('sum(`learnedTime`) as learnedTime, `userId`')
            ->groupBy('userId');

        return $builder->execute()->fetchAll();
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
                'event = :event_EQ',
                'event <> :event_NEQ',
                'userId = :userId',
                'userId IN ( :userIds )',
                'learnedTime >= :learnedTime_GE',
                'learnedTime <= :learnedTime_LE',
                'createdTime >= :createdTime_GE',
                'createdTime <= :createdTime_LE',
                'createdTime < :createdTime_LT',
            ),
        );
    }

    /**
     * @return Lock
     */
    protected function getLock()
    {
        if (!$this->lock) {
            $this->lock = new Lock($this->biz);
        }

        return $this->lock;
    }
}
