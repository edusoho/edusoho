<?php

namespace AppBundle\Component\Export\UserLearnStatistics;

use AppBundle\Common\MathToolkit;
use AppBundle\Component\Export\Exporter;
use AppBundle\Common\ArrayToolkit;

class UserLearnStatisticsExporter extends Exporter
{
    private $orderBy = array();

    public function canExport()
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    public function getCount()
    {
        return $this->getUserService()->countUsers(ArrayToolkit::parts($this->conditions, array('userIds')));
    }

    public function getContent($start, $limit)
    {
        $users = $this->getUserService()->searchUsers(
            ArrayToolkit::parts($this->conditions, array('userIds')),
            array('id' => 'DESC'),
            $start,
            $limit
        );

        $conditions = array_merge($this->conditions, array('userIds' => ArrayToolkit::column($users, 'id')));

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
        $statisticsContent = array();

        foreach ($users as $key => $user) {
            $member = array();
            $statistic = !empty($statistics[$user['id']]) ? $statistics[$user['id']] : false;

            if ($statistic) {
                $member[] = $user['nickname'];
                $member[] = $statistic['joinedClassroomNum'];
                $member[] = $statistic['exitClassroomNum'];
                $member[] = $statistic['joinedCourseNum'];
                $member[] = $statistic['exitCourseNum'];
                $member[] = $statistic['finishedTaskNum'];
                $member[] = number_format($statistic['learnedSeconds'] / 60, 2, '.', ',');
                $member[] = MathToolkit::simple($statistic['actualAmount'], 0.01);
            } else {
                $member = array($user['nickname'], 0, 0, 0, 0, 0, 0, 0);
            }

            $statisticsContent[] = $member;
        }

        return $statisticsContent;
    }

    public function getTitles()
    {
        return array('user.learn.statistics.nickname', 'user.learn.statistics.join.classroom.num', 'user.learn.statistics.exit.classroom.num', 'user.learn.statistics.join.course.num', 'user.learn.statistics.exit.course.num', 'user.learn.statistics.finished.task.num', 'user.learn.statistics.learned.seconds', 'user.learn.statistics.actual.amount');
    }

    public function buildCondition($conditions)
    {
        if (!empty($conditions['nickname'])) {
            $users = $this->getUserService()->searchUsers(
                array('nickname' => $conditions['nickname']),
                array(),
                0,
                PHP_INT_MAX
            );

            $conditions['userIds'] = ArrayToolkit::column($users, 'id');
            unset($conditions['nickname']);
        } else {
            $conditions['userIds'] = array();
        }

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
}
