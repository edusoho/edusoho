<?php

namespace AppBundle\Twig;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use Biz\AI\Util\AgentToken;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AIExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('ai_agent_token', [$this, 'makeAIAgentToken']),
            new \Twig_SimpleFunction('is_course_show_ai_agent', [$this, 'isCourseShowAIAgent']),
            new \Twig_SimpleFunction('is_course_task_show_ai_agent', [$this, 'isCourseTaskShowAIAgent']),
            new \Twig_SimpleFunction('is_answer_show_ai_agent', [$this, 'isAnswerShowAIAgent']),
            new \Twig_SimpleFunction('get_course_chat_meta_data', [$this, 'getCourseChatMetaData']),
            new \Twig_SimpleFunction('get_lesson_chat_meta_data', [$this, 'getLessonChatMetaData']),
            new \Twig_SimpleFunction('get_answer_chat_meta_data', [$this, 'getAnswerChatMetaData']),
            new \Twig_SimpleFunction('is_study_plan_generated', [$this, 'isStudyPlanGenerated']),
        ];
    }

    public function makeAIAgentToken()
    {
        return (new AgentToken())->make();
    }

    public function isCourseShowAIAgent($courseId)
    {
        if (!$this->getCourseMemberService()->isCourseStudent($courseId, $this->getCurrentUser()->getId())) {
            return false;
        }
        $course = $this->getCourseService()->getCourse($courseId);
        $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());
        if ($this->container->get('web.twig.course_extension')->isMemberExpired($course, $courseMember)) {
            return false;
        }
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);

        return !empty($agentConfig['isActive']);
    }

    public function isCourseTaskShowAIAgent($courseId, $taskId)
    {
        if (!$this->getCourseMemberService()->isCourseStudent($courseId, $this->getCurrentUser()->getId())) {
            return false;
        }
        $course = $this->getCourseService()->getCourse($courseId);
        $courseMember = $this->getCourseMemberService()->getCourseMember($courseId, $this->getCurrentUser()->getId());
        if ($this->container->get('web.twig.course_extension')->isMemberExpired($course, $courseMember)) {
            return false;
        }
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);
        if (empty($agentConfig['isActive'])) {
            return false;
        }
        $task = $this->getTaskService()->getTask($taskId);
        if ('testpaper' != $task['type']) {
            return true;
        }
        $taskResult = $this->getTaskResultService()->getTaskResultByTaskIdAndUserId($taskId, $this->getCurrentUser()->getId());
        if (!empty($taskResult) && 'finish' == $taskResult['status']) {
            return true;
        }

        return false;
    }

    public function isAnswerShowAIAgent($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $module = $this->getItemBankExerciseModuleService()->getByAnswerSceneId($answerRecord['answer_scene_id']);
        if (empty($module)) {
            return false;
        }
        $exerciseBinds = $this->getItemBankExerciseService()->findExerciseBindByExerciseId($module['exerciseId']);
        if (empty($exerciseBinds)) {
            return false;
        }
        foreach ($exerciseBinds as $exerciseBind) {
            if ('course' == $exerciseBind['bindType']) {
                $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($exerciseBind['bindId']);
                if (!empty($agentConfig['isActive'])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getCourseChatMetaData($course)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($course['id']);

        return json_encode([
            'workerUrl' => $this->getAgentWorkerUrl(),
            'domainId' => $agentConfig['domainId'] ?? '',
            'courseId' => $course['id'],
            'courseName' => $course['courseSetTitle'],
        ]);
    }

    public function getLessonChatMetaData($course, $task)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($course['id']);

        return json_encode([
            'workerUrl' => $this->getAgentWorkerUrl(),
            'domainId' => $agentConfig['domainId'] ?? '',
            'courseId' => $course['id'],
            'courseName' => $course['courseSetTitle'],
            'lessonId' => $task['id'],
            'lessonName' => $task['title'],
        ]);
    }

    public function getAnswerChatMetaData($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        $module = $this->getItemBankExerciseModuleService()->getByAnswerSceneId($answerRecord['answer_scene_id']);
        if (empty($module)) {
            return '';
        }
        $exerciseBinds = $this->getItemBankExerciseService()->findExerciseBindByExerciseId($module['exerciseId']);
        if (empty($exerciseBinds)) {
            return '';
        }
        foreach ($exerciseBinds as $exerciseBind) {
            if ('course' == $exerciseBind['bindType']) {
                $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($exerciseBind['bindId']);
                if (!empty($agentConfig['isActive'])) {
                    $course = $this->getCourseService()->getCourse($agentConfig['courseId']);

                    return json_encode([
                        'workerUrl' => $this->getAgentWorkerUrl(),
                        'domainId' => $agentConfig['domainId'],
                        'courseId' => $course['id'],
                        'courseName' => $course['courseSetTitle'],
                    ]);
                }
            }
        }

        return '';
    }

    public function isStudyPlanGenerated($courseId)
    {
        return $this->getStudyPlanService()->isUserStudyPlanGenerated($this->getCurrentUser()->getId(), $courseId) ? 1 : 0;
    }

    private function getAgentWorkerUrl()
    {
        return $this->container->get('router')->generate('agent_worker', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @return CurrentUser
     */
    private function getCurrentUser()
    {
        return $this->biz['user'];
    }

    /**
     * @return MemberService
     */
    private function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    private function getTaskResultService()
    {
        return $this->biz->service('Task:TaskResultService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return AnswerRecordService
     */
    private function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ExerciseModuleService
     */
    private function getItemBankExerciseModuleService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ExerciseService
     */
    private function getItemBankExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return AgentConfigService
     */
    private function getAgentConfigService()
    {
        return $this->biz->service('AgentBundle:AgentConfig:AgentConfigService');
    }

    /**
     * @return StudyPlanService
     */
    private function getStudyPlanService()
    {
        return $this->biz->service('AgentBundle:StudyPlan:StudyPlanService');
    }
}
