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
                $member[] = $statistic['learnedSeconds'] / 60;
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
        return array('用户名', '加入班级数', '退出班级数', '加入计划数', '退出计划数', '学完任务数', '学习时长(分)', '消费总额(元)');
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
        } else {
            $conditions['userIds'] = array();
        }

        if (!empty($conditions['isDefault']) && $conditions['isDefault'] == 'true') {
            $this->orderBy = array('userId' => 'DESC', 'joinedCourseNum' => 'DESC', 'actualAmount' => 'DESC');
        } else {
            $this->orderBy = array('id' => 'DESC');
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
