<?php

namespace AgentBundle\Workflow;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AppBundle\Common\DateToolkit;
use AppBundle\Common\TimeMachine;
use Biz\Activity\Service\ActivityService;
use Biz\AI\Service\AIService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractWorkflow implements Workflow
{
    protected $biz;

    function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    protected function convertDateToCN($date)
    {
        return date('Y年m月d日', strtotime($date));
    }

    protected function makeChineseWeekDays($weekDays)
    {
        $chineseWeekdays = [];
        foreach ($weekDays as $weekDay) {
            $chineseWeekday = $this->convertWeekDayToCN($weekDay);
            if (!empty($chineseWeekday)) {
                $chineseWeekdays[] = $chineseWeekday;
            }
        }

        return implode('、', $chineseWeekdays);
    }

    protected function convertWeekDayToCN($weekday)
    {
        return DateToolkit::convertToZHWeekday($weekday);
    }

    protected function convertSecondsToCN($seconds)
    {
        return TimeMachine::formatSecondsToZH($seconds);
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }

    /**
     * @return AgentConfigService
     */
    protected function getAgentConfigService()
    {
        return $this->biz->service('AgentBundle:AgentConfig:AgentConfigService');
    }

    /**
     * @return StudyPlanService
     */
    protected function getStudyPlanService()
    {
        return $this->biz->service('AgentBundle:StudyPlan:StudyPlanService');
    }

    /**
     * @return AIService
     */
    protected function getAIService()
    {
        return $this->biz->service('AI:AIService');
    }
}
