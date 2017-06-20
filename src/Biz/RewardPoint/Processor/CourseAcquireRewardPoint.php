<?php

namespace Biz\RewardPoint\Processor;

class CourseAcquireRewardPoint extends RewardPoint
{
    public function canReward($params)
    {
        return $this->verifySettingEnable() && $this->canWave($params);
    }

    public function generateFlow($params)
    {
        $flow = array(
            'userId' => $params['userId'],
            'type' => 'inflow',
            'amount' => $this->getAmount($params),
            'targetId' => $params['targetId'],
            'targetType' => $params['targetType'],
            'way' => $params['way'],
            'operator' => $this->getUser()->getId(),
        );

        return $flow;
    }

    public function verifySettingEnable()
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

    public function canWave($params)
    {
        $result = false;
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($params['targetId']);
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);
        $flow = $this->getAccountFlowService()->getInflowByUserIdAndTarget($params['userId'], $params['targetId'], $params['targetType']);

        if ($taskResult['status'] == 'finish' && empty($flow)) {
            $result = true;
            $this->waveCourseRewardPoint($params);
        }

        if ($course['taskRewardPoint'] <= 0) {
            $result = false;
        }

        return $result;
    }

    protected function waveCourseRewardPoint($params)
    {
        $result = $this->canWaveCourseRewardPoint($params['targetId']);

        if ($result) {
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($params['targetId']);

            $params = array(
                'way' => 'course_reward_point',
                'targetId' => $taskResult['courseId'],
                'targetType' => 'course',
                'userId' => $taskResult['userId'],
            );

            $flow = $this->keepFlow($this->generateFlow($params));
            $this->waveRewardPoint($flow['userId'], $flow['amount']);
        }
    }

    protected function canWaveCourseRewardPoint($taskId)
    {
        $result = false;
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);
        $course = $this->getCourseService()->getCourse($taskResult['courseId']);
        $member = $this->getCourseMemberService()->getCourseMember($course['id'], $taskResult['userId']);
        $flow = $this->getAccountFlowService()->getInflowByUserIdAndTarget($member['userId'], $course['id'], 'course');

        if ($course['serializeMode'] != 'serialized' && $member['learnedNum'] >= $course['publishedTaskNum'] && empty($flow) && $course['rewardPoint'] > 0) {
            $result = true;
        }

        return $result;
    }

    protected function getAmount($params)
    {
        $amount = 0;

        if ($params['targetType'] == 'task') {
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($params['targetId']);
            $course = $this->getCourseService()->getCourse($taskResult['courseId']);
            $amount = $course['taskRewardPoint'];
        } else {
            $course = $this->getCourseService()->getCourse($params['targetId']);
            $amount = $course['rewardPoint'];
        }

        return $amount;
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
