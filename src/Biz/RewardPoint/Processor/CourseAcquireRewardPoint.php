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
                $this->getTaskResultService()->updateTaskResult($taskResult['id'], array('isAcquiredRewardPoint' => 1));
            }

            if ($course['serializeMode'] != 'serialized' && !$member['isAcquiredRewardPoint'] && $course['rewardPoint'] > 0) {
                if ($member['learnedNum'] >= $course['publishedTaskNum']) {
                    $this->waveRewardPoint($user['id'], $course['rewardPoint']);
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