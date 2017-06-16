<?php

namespace Biz\RewardPoint\Processor;

class CourseAcquireRewardPoint extends RewardPoint
{
    public function circulatingRewardPoint($taskId)
    {
        $result = $this->verifySettingEnable();

        if ($result) {
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);
            $course = $this->getCourseService()->getCourse($taskResult['courseId']);
            $member = $this->getCourseMemberService()->getCourseMember($course['id'], $taskResult['userId']);

            $this->circulatingTaskRewardPoint($taskResult, $course);
            $this->circulatingCourseRewardPoint($member, $course);
        }
    }

    public function verifySettingEnable($params = array())
    {
        $settings = $this->getSettingService()->get('reward_point', array());
        $result = false;
        if (!empty($settings)) {
            if (isset($settings['enable']) && $settings['enable'] == 1) {
                $result = true;
            }
        }

        return $result;
    }

    public function canCirculating($params)
    {
        $result = false;
        $user = $this->getUser();
        $flow = $this->getAccountFlowService()->getInflowByUserIdAndTarget($user['id'], $params['targetId'], $params['targetType']);

        if (empty($flow)) {
            $result = true;
        }

        return $result;
    }

    protected function circulatingTaskRewardPoint($taskResult, $course)
    {
        $result = $this->canCirculating(array('targetId' => $taskResult['courseTaskId'], 'targetType' => 'task'));
        if ($taskResult['status'] == 'finish' && $result) {
            $this->waveRewardPoint($taskResult['userId'], $course['taskRewardPoint']);
            $flow = array(
                'userId' => $taskResult['userId'],
                'type' => 'inflow',
                'amount' => $course['taskRewardPoint'],
                'targetId' => $taskResult['courseTaskId'],
                'targetType' => 'task',
                'way' => 'task_reward_point',
                'operator' => 0,
            );
            $this->keepFlow($flow);
            $user['Reward-Point-Notify'] = array('type' => 'inflow', 'amount' => $flow['amount'], 'way' => 'task_reward_point');
        }
    }

    protected function circulatingCourseRewardPoint($member, $course)
    {
        $result = $this->canCirculating(array('targetId' => $course['id'], 'targetType' => 'course'));
        if ($course['serializeMode'] != 'serialized' && $result) {
            if ($member['learnedNum'] >= $course['publishedTaskNum']) {
                $this->waveRewardPoint($member['userId'], $course['rewardPoint']);
                $flow = array(
                    'userId' => $member['userId'],
                    'type' => 'inflow',
                    'amount' => $course['rewardPoint'],
                    'targetId' => $course['id'],
                    'targetType' => 'course',
                    'way' => 'course_reward_point',
                    'operator' => 0,
                );
                $this->keepFlow($flow);
                $user['Reward-Point-Notify'] = array('type' => 'inflow', 'amount' => $flow['amount'], 'way' => 'course_reward_point');
            }
        }
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
