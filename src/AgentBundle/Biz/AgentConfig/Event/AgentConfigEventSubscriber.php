<?php

namespace AgentBundle\Biz\AgentConfig\Event;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\AI\Service\AIService;
use Biz\AI\Util\AgentToken;
use Biz\Course\Service\CourseService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\Question\Traits\QuestionFlatTrait;
use Biz\WrongBook\Service\WrongQuestionService;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Monolog\Logger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AgentConfigEventSubscriber extends EventSubscriber
{
    use QuestionFlatTrait;

    public static function getSubscribedEvents()
    {
        return [
            'agentConfig.create' => 'onAgentConfigCreate',
            'course.delete' => 'onCourseDelete',
            'course-set.update' => 'onCourseSetUpdate',
            'activity.create' => 'onActivityCreate',
            'activity.update' => 'onActivityUpdate',
            'activity.delete' => 'onActivityDelete',
            'answer.submitted' => 'onAnswerSubmitted',
        ];
    }

    public function onAgentConfigCreate(Event $event)
    {
        $agentConfig = $event->getSubject();
        $activities = $this->getActivityService()->search(['fromCourseId' => $agentConfig['courseId']], [], 0, PHP_INT_MAX);
        $updateActivities = [];
        $cloudFileActivityIds = [];
        foreach ($activities as $activity) {
            if ('text' == $activity['mediaType']) {
                $document = $this->getAIService()->createDocumentByText([
                    'datasetId' => $agentConfig['datasetId'],
                    'extId' => $activity['id'],
                    'name' => $activity['title'],
                    'content' => strip_tags($activity['content']),
                ]);
                $updateActivities[$activity['id']] = ['documentId' => $document['id']];
            }
            if (in_array($activity['mediaType'], ['audio', 'doc', 'ppt', 'video'])) {
                $cloudFileActivityIds[] = $activity['id'];
            }
        }
        $cloudFileActivities = $this->getActivityService()->findActivities($cloudFileActivityIds, true, 0);
        $objects = [];
        foreach ($cloudFileActivities as $cloudFileActivity) {
            if (!empty($cloudFileActivity['ext']['file']['globalId'])) {
                $objects[] = [
                    'name' => $cloudFileActivity['title'],
                    'objectKey' => $cloudFileActivity['ext']['file']['globalId'],
                    'objectVendor' => 'escloud',
                    'extId' => $cloudFileActivity['id'],
                ];
            }
        }
        if (empty($objects)) {
            return;
        }
        $documents = $this->getAIService()->batchCreateDocumentByObject($agentConfig['datasetId'], $objects);
        foreach ($documents as $document) {
            $updateActivities[$document['extId']] = ['documentId' => $document['id']];
        }
        $this->getActivityDao()->batchUpdate(array_keys($updateActivities), $updateActivities);
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($course['id']);
        if (empty($agentConfig)) {
            return;
        }
        try {
            $this->getAIService()->deleteDataset($agentConfig['datasetId']);
        } catch (\Exception $e) {
            $this->getLogger()->error('delete dataset error: '.$e->getMessage());
        }
        $this->getAgentConfigService()->deleteAgentConfig($agentConfig['id']);
    }

    public function onCourseSetUpdate(Event $event)
    {
        if (!$event->hasArgument('oldCourseSet')) {
            return;
        }
        $courseSet = $event->getSubject();
        $oldCourseSet = $event->getArgument('oldCourseSet');
        if ($courseSet['title'] == $oldCourseSet['title']) {
            return;
        }
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
        $agentConfigs = $this->getAgentConfigService()->findAgentConfigsByCourseIds(array_column($courses, 'id'));
        foreach ($agentConfigs as $agentConfig) {
            try {
                $this->getAIService()->updateDataset($agentConfig['datasetId'], ['name' => $courseSet['title']]);
            } catch (\Exception $e) {
                $this->getLogger()->error('update dataset error: '.$e->getMessage());
            }
        }
    }

    public function onActivityCreate(Event $event)
    {
        $activity = $event->getSubject();
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($activity['fromCourseId']);
        if (empty($agentConfig)) {
            return;
        }
        $this->createDatasetDocumentIfNecessary($agentConfig['datasetId'], $activity);
    }

    public function onActivityUpdate(Event $event)
    {
        $activity = $event->getSubject();
        if (empty($activity['documentId'])) {
            return;
        }
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($activity['fromCourseId']);
        if (empty($agentConfig)) {
            return;
        }
        try {
            $this->getAIService()->deleteDocument($activity['documentId']);
        } catch (\Exception $e) {
            $this->getLogger()->error('delete document error: '.$e->getMessage());
        }
        $this->createDatasetDocumentIfNecessary($agentConfig['datasetId'], $activity);
    }

    public function onActivityDelete(Event $event)
    {
        $activity = $event->getSubject();
        if (!empty($activity['documentId'])) {
            try {
                $this->getAIService()->deleteDocument($activity['documentId']);
            } catch (\Exception $e) {
                $this->getLogger()->error('delete document error: '.$e->getMessage());
            }
        }
    }

    public function onAnswerSubmitted(Event $event)
    {
        $answerRecord = $event->getSubject();
        $courseId = $this->getRelatedCourseId($answerRecord['answer_scene_id']);
        if (empty($courseId)) {
            return;
        }
        $domainId = $this->getDomainIdByCourseId($courseId);
        if (empty($domainId)) {
            return;
        }
        $wrongAnswerQuestionReports = $this->getAnswerQuestionReportService()->search([
            'answer_record_id' => $answerRecord['id'],
            'statues' => ['wrong', 'part_right'],
        ], [], 0, PHP_INT_MAX, ['question_id', 'response']);
        $wrongAnswerQuestionReports = array_column($wrongAnswerQuestionReports, null, 'question_id');
        $questions = $this->getItemService()->findQuestionsByQuestionIdsIncludeDeleted(array_column($wrongAnswerQuestionReports, 'question_id'));
        $flatQuestions = [];
        foreach ($questions as $question) {
            $type = $this->modeToType[$question['answer_mode']];
            $flatQuestions[] = "{$this->flattenMain($type, $question)}{$this->flattenAnswer($type, $question)}{$this->flattenWrongAnswer($type, $wrongAnswerQuestionReports[$question['id']]['response'])}{$this->flattenAnalysis($question)}";
        }
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);
        $biz = $this->getBiz();
        try {
            $this->getAIService()->asyncRunWorkflow('teacher.question.analysis-weaknesses', [
                'domainId' => $domainId,
                'userId' => $biz['user']['id'],
                'questions' => $flatQuestions,
                'datasets' => [$agentConfig['datasetId']],
            ], $this->generateUrl('workflow_callback', ['workflow' => 'analysis-weaknesses', 'token' => (new AgentToken())->make()]));
        } catch (\Exception $e) {
            $this->getLogger()->error('async run workflow error: '.$e->getMessage());
        }
    }

    private function createDatasetDocumentIfNecessary($datasetId, $activity)
    {
        if ('text' == $activity['mediaType']) {
            try {
                $document = $this->getAIService()->createDocumentByText([
                    'datasetId' => $datasetId,
                    'extId' => $activity['id'],
                    'name' => $activity['title'],
                    'content' => $activity['content'],
                ]);
            } catch (\Exception $e) {
                $this->getLogger()->error('create document by text error: '.$e->getMessage());
            }
        }
        if (in_array($activity['mediaType'], ['audio', 'doc', 'ppt', 'video'])) {
            $activity = $this->getActivityService()->getActivity($activity['id'], true);
            try {
                if (!empty($activity['ext']['file']['globalId'])) {
                    $document = $this->getAIService()->createDocumentByObject([
                        'datasetId' => $datasetId,
                        'extId' => $activity['id'],
                        'name' => $activity['title'],
                        'resNo' => $activity['ext']['file']['globalId'],
                    ]);
                }
            } catch (\Exception $e) {
                $this->getLogger()->error('create document by object error: '.$e->getMessage());
            }
        }
        if (!empty($document)) {
            $this->getActivityDao()->update($activity['id'], ['documentId' => $document['id']]);
        }
    }

    private function getRelatedCourseId($answerSceneId)
    {
        $activity = $this->getActivityService()->getActivityByAnswerSceneId($answerSceneId);
        if (!empty($activity)) {
            return $activity['fromCourseId'];
        }
        $module = $this->getItemBankExerciseModuleService()->getByAnswerSceneId($answerSceneId);
        if (!empty($module)) {
            $exerciseBinds = $this->getItemBankExerciseService()->findExerciseBindByExerciseId($module['exerciseId']);
            if (empty($exerciseBinds)) {
                return false;
            }
            foreach ($exerciseBinds as $exerciseBind) {
                if ('course' != $exerciseBind['bindType']) {
                    continue;
                }
                $domainId = $this->getDomainIdByCourseId($exerciseBind['bindId']);
                if (!empty($domainId)) {
                    return $exerciseBind['bindId'];
                }
            }
            return false;
        }
        $pool = $this->getWrongQuestionService()->getPoolBySceneId($answerSceneId);
        if (empty($pool) || 'course' != $pool['target_type']) {
            return false;
        }

        return $pool['target_id'];
    }

    private function getDomainIdByCourseId($courseId)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($courseId);
        if (!empty($agentConfig['isActive']) && !empty($agentConfig['isDiagnosisActive'])) {
            return $agentConfig['domainId'];
        }

        return false;
    }

    private function generateUrl($route, $parameters, $referenceType = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        global $kernel;

        return $kernel->getContainer()->get('router')->generate($route, $parameters, $referenceType);
    }

    /**
     * @return Logger
     */
    private function getLogger()
    {
        return $this->getBiz()['logger'];
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerQuestionReportService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->getBiz()->service('ItemBank:Item:ItemService');
    }

    /**
     * @return ExerciseModuleService
     */
    private function getItemBankExerciseModuleService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return ExerciseService
     */
    private function getItemBankExerciseService()
    {
        return $this->getBiz()->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->getBiz()->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return AgentConfigService
     */
    private function getAgentConfigService()
    {
        return $this->getBiz()->service('AgentBundle:AgentConfig:AgentConfigService');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->getBiz()->service('AI:AIService');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->getBiz()->dao('Activity:ActivityDao');
    }
}
