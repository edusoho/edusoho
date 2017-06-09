<?php

namespace Biz\RewardPoint\Processor;

class CourseAcquireRewardPoint extends AcquireRewardPoint
{
    public function circulatingRewardPoint($taskId)
    {
        $user = $this->getUser();
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);
        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $user['id']);
        $settings = $this->getSettingService()->get('reward_point', array());

        $result = $this->verifySettingEnable($settings);

        if ($result) {
            if ($taskResult['status'] == 'finish' && !$taskResult['isAcquiredRewardPoint'] && $course['taskRewardPoint'] > 0) {
                $this->waveRewardPoint($user['id'], $course['taskRewardPoint']);
                $flow = array(
                    'userId' => $user['id'],
                    'type' => 'inflow',
                    'amount' => $course['taskRewardPoint'],
                    'targetId' => $taskId,
                    'targetType' => 'task',
                    'way' => 'task_reward_point',
                );
                $this->keepFlow($flow);
                $this->getTaskResultService()->updateTaskResult($taskResult['id'], array('isAcquiredRewardPoint' => 1));
            }

            if ($course['serializeMode'] != 'serialized' && !$member['isAcquiredRewardPoint'] && $course['rewardPoint'] > 0) {
                if ($member['learnedNum'] >= $course['publishedTaskNum']) {
                    $this->waveRewardPoint($user['id'], $course['rewardPoint']);
                    $flow = array(
                        'userId' => $user['id'],
                        'type' => 'inflow',
                        'amount' => $course['rewardPoint'],
                        'targetId' => $course['id'],
                        'targetType' => 'course',
                        'way' => 'course_reward_point',
                    );
                    $this->keepFlow($flow);
                    $this->getCourseMemberService()->updateMember($member['id'], array('isAcquiredRewardPoint' => 1));
                }
            }
        }
    }

    private function verifySettingEnable()
    {
        $result = false;
        if (!empty($settings)) {
            if (isset($settings['enable']) && $settings['enable'] == 1) {
                $result = true;
            }
        }

        return $result;
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
