<?php

namespace AgentBundle\Workflow;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use Biz\Activity\Service\ActivityService;
use Biz\AI\Service\AIService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Context\Biz;

abstract class AbstractWorkflow implements Workflow
{
    protected $biz;

    function __construct(Biz $biz)
    {
        $this->biz = $biz;
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
