<?php

namespace Biz\LiveStatistics\Dao\Impl;

use Biz\LiveStatistics\Dao\LiveMemberStatisticsDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\Framework\Dao\DynamicQueryBuilder;

class LiveMemberStatisticsDaoImpl extends AdvancedDaoImpl implements LiveMemberStatisticsDao
{
    protected $table = 'live_statistics_member_data';

    public function searchLiveMembersJoinCourseMember($conditions, $start, $limit)
    {
        $join = isset($conditions['liveId']) ? 'member.userId=live.userId and live.liveId = '.$conditions['liveId'] : 'member.userId=live.userId';
        $this->conditionFilter($conditions);
        $builder = new DynamicQueryBuilder($this->db(), $conditions);
        $builder->select('live.*,member.userId,member.id,member.courseId')
            ->from('course_member', 'member')
            ->leftJoin('member', 'live_statistics_member_data', 'live', $join)
            ->andWhere('member.userId IN ( :userIds )')
            ->andWhere('member.userId = :userId')
            ->andWhere('member.userId NOT IN (:excludeUserIds)')
            ->andWhere('member.courseId = :courseId')
            ->orderBy('live.watchDuration', 'DESC')
            ->setFirstResult((int) $start)
            ->setMaxResults((int) $limit);

        return $builder->execute()->fetchAll();
    }

    public function findMembersByLiveIds($liveIds){
        return $this->findInField('liveId', $liveIds);
    }

    public function sumWatchDurationByLiveId($liveId, $userIds = [])
    {
        $sql = 'SELECT sum(`watchDuration`) FROM `live_statistics_member_data` WHERE  `liveId` = ? ';
        if (!empty($userIds)) {
            $marks = str_repeat('?,', count($userIds) - 1).'?';
            $sql = $sql." and userId IN ({$marks})";

            return $this->db()->fetchColumn($sql, array_merge([$liveId], $userIds));
        } else {
            return $this->db()->fetchColumn($sql, [$liveId]);
        }
    }

    public function sumChatNumByLiveId($liveId, $userIds = [])
    {
        $sql = 'SELECT sum(`chatNum`) FROM `live_statistics_member_data` WHERE  `liveId` = ? ';
        if (!empty($userIds)) {
            $marks = str_repeat('?,', count($userIds) - 1).'?';
            $sql = $sql." and userId IN ({$marks})";

            return $this->db()->fetchColumn($sql, array_merge([$liveId], $userIds));
        } else {
            return $this->db()->fetchColumn($sql, [$liveId]);
        }
    }

    protected function conditionFilter(&$conditions)
    {
        $conditions = array_filter(
            $conditions,
            function ($v) {
                if (0 === $v) {
                    return true;
                }

                if (empty($v)) {
                    return false;
                }

                return true;
            }
        );
    }

    public function declares()
    {
        return [
            'serializes' => [],
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'liveId = :liveId',
                'courseId = :courseId',
                'liveId IN (:liveIds)',
                'userId IN (:userIds)',
                'requestTime <= :requestTime_LT',
                'requestTime >= :requestTime_GE',
            ],
        ];
    }
}
