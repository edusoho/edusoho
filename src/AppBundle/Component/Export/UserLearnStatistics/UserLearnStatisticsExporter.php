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
        return $this->getUserService()->countUsers(ArrayToolkit::parts($this->conditions, ['userIds', 'destroyed']));
    }

    public function getContent($start, $limit)
    {
        $users = $this->getUserService()->searchUsers(
            ArrayToolkit::parts($this->conditions, ['userIds', 'destroyed']),
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

            if ($statistic) {
                $member[] = $user['nickname'];
                $member[] = $statistic['joinedClassroomNum'];
                $member[] = $statistic['exitClassroomNum'];
                $member[] = $statistic['joinedCourseNum'];
                $member[] = $statistic['exitCourseNum'];
                $member[] = $statistic['finishedTaskNum'];
                $member[] = empty($userStatistics) ? 0 : array_sum(ArrayToolkit::column($userStatistics, 'sumTime'));
                $member[] = empty($userStatistics) ? 0 : array_sum(ArrayToolkit::column($userStatistics, 'pureTime'));
                $member[] = MathToolkit::simple($statistic['actualAmount'], 0.01);
            } else {
                $member = [$user['nickname'], 0, 0, 0, 0, 0, 0, 0, 0];
            }

            $statisticsContent[] = $member;
        }

        return $statisticsContent;
    }

    public function getTitles()
    {
        return [
            'user.learn.statistics.nickname',
            'user.learn.statistics.join.classroom.num',
            'user.learn.statistics.exit.classroom.num',
            'user.learn.statistics.join.course.num',
            'user.learn.statistics.exit.course.num',
            'user.learn.statistics.finished.task.num',
            'user.learn.statistics.sum_learn_time',
            'user.learn.statistics.pure_learn_time',
            'user.learn.statistics.actual.amount',
        ];
    }

    public function buildCondition($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $users = $this->getUserService()->searchUsers(
                ['nickname' => $conditions['nickname']],
                [],
                0,
                PHP_INT_MAX
            );

            $conditions['userIds'] = ArrayToolkit::column($users, 'id');
            unset($conditions['nickname']);
        } else {
            $conditions['userIds'] = [];
        }

        $conditions['destroyed'] = 0;

        return $conditions;
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
