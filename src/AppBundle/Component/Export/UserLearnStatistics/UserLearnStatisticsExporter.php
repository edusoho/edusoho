<?php

namespace AppBundle\Component\Export\UserLearnStatistics;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\MathToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Visualization\Service\ActivityLearnDataService;

class UserLearnStatisticsExporter extends Exporter
{
    private $orderBy = [];

    public function canExport()
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    public function getCount()
    {
        return $this->getUserService()->countUsers([ArrayToolkit::parts($this->conditions, ['userIds', 'destroyed']),'isStudent' => 0]);
    }

    public function getContent($start, $limit)
    {
        $users = $this->getUserService()->searchUsers(
            [ArrayToolkit::parts($this->conditions, ['userIds', 'destroyed']),'isStudent' => 0],
            ['id' => 'DESC'],
            $start,
            $limit
        );

        $conditions = array_merge($this->conditions, ['userIds' => ArrayToolkit::column($users, 'id')]);

        $statistics = $this->getLearnStatisticsService()->statisticsDataSearch(
            $conditions,
            $this->orderBy
        );

        $statisticsContent = $this->handlerStatistics($statistics, $users);

        return $statisticsContent;
    }

    protected function handlerStatistics($statistics, $users)
    {
        $statistics = ArrayToolkit::index($statistics, 'userId');
        $statisticsContent = [];

        foreach ($users as $key => $user) {
            $member = [];
            $conditions = array_merge($this->conditions, ['userId' => $user['id']]);
            $userStatistics = $this->getActivityLearnDataService()->searchUserLearnDailyData(
                $conditions,
                [],
                0,
                PHP_INT_MAX,
                ['userId', 'sumTime', 'pureTime']
            );
            $statistic = !empty($statistics[$user['id']]) ? $statistics[$user['id']] : false;
            $nickname = is_numeric($user['nickname']) ? $user['nickname']."\t" : $user['nickname'];
            $sumTime = empty($userStatistics) ? 0 : round(array_sum(ArrayToolkit::column($userStatistics, 'sumTime')) / 60, 1);

            if ($statistic) {
                $member[] = $nickname;
                $member[] = empty($user['verifiedMobile']) ? '--' : $user['verifiedMobile']."\t";
                $member[] = $statistic['joinedClassroomNum'];
                $member[] = $statistic['exitClassroomNum'];
                $member[] = $statistic['joinedCourseNum'];
                $member[] = $statistic['exitCourseNum'];
                $member[] = $statistic['finishedTaskNum'];
                $member[] = $sumTime;
                $member[] = MathToolkit::simple($statistic['actualAmount'], 0.01);
            } else {
                $member = [
                    $nickname,
                    empty($user['verifiedMobile']) ? '--' : $user['verifiedMobile']."\t",
                    0,
                    0,
                    0,
                    0,
                    0,
                    $sumTime,
                    0,
                ];
            }

            $statisticsContent[] = $member;
        }

        return $statisticsContent;
    }

    public function getTitles()
    {
        return [
            'user.learn.statistics.student_nickname',
            'user.learn.statistics.mobile',
            'user.learn.statistics.join.classroom.num',
            'user.learn.statistics.exit.classroom.num',
            'user.learn.statistics.join.course.num',
            'user.learn.statistics.exit.course.num',
            'user.learn.statistics.finished.task.num',
            'user.learn.statistics.sum_learn_time',
            'user.learn.statistics.actual.amount',
        ];
    }

    public function buildCondition($conditions)
    {
        $conditions['userIds'] = [];
        if (!empty($conditions['keyword'])) {
            $userConditions = ['nickname' => $conditions['keyword']];
            if ('mobile' == $conditions['keywordType']) {
                unset($userConditions['nickname']);
                $userConditions['verifiedMobile'] = $conditions['keyword'];
            }
            $users = $this->getUserService()->searchUsers(
                $userConditions,
                [],
                0,
                PHP_INT_MAX,
                ['id']
            );
            $conditions['userIds'] = ArrayToolkit::column($users, 'id');
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        $conditions['destroyed'] = 0;
        return $conditions;
    }

    protected function getPageConditions()
    {
        return [$this->parameter['start'], 100];
    }

    protected function getLearnStatisticsService()
    {
        return $this->getBiz()->service('UserLearnStatistics:LearnStatisticsService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return ActivityLearnDataService
     */
    protected function getActivityLearnDataService()
    {
        return $this->getBiz()->service('Visualization:ActivityLearnDataService');
    }
}
