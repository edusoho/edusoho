<?php

namespace AgentBundle\Biz\AgentConfig\Event;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\AI\Service\AIService;
use Biz\Course\Service\CourseService;
use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class AgentConfigEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'agentConfig.create' => 'onAgentConfigCreate',
            'course.delete' => 'onCourseDelete',
            'course-set.update' => 'onCourseSetUpdate',
            'activity.create' => 'onActivityCreate',
            'activity.update' => 'onActivityUpdate',
            'activity.delete' => 'onActivityDelete',
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
                    'content' => $activity['content'],
                ]);
                $updateActivities[$activity['id']] = ['documentId' => $document['id']];
            }
            if (in_array($activity['mediaType'], ['audio', 'doc', 'ppt', 'video'])) {
                $cloudFileActivityIds[] = $activity['id'];
            }
        }
        $cloudFileActivities = $this->getActivityService()->findActivities($cloudFileActivityIds, true, 0);
        $items = [];
        foreach ($cloudFileActivities as $cloudFileActivity) {
            $items[] = [
                'extId' => $cloudFileActivity['id'],
                'name' => $cloudFileActivity['title'],
                'resNo' => $cloudFileActivity['ext']['file']['globalId'],
            ];
        }
        $documents = $this->getAIService()->batchCreateDocumentByResource($agentConfig['datasetId'], $items);
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
        $this->getAIService()->deleteDataset($agentConfig['datasetId']);
    }

    public function onCourseSetUpdate(Event $event)
    {
        $courseSet = $event->getSubject();
        $oldCourseSet = $event->getArgument('oldCourseSet');
        if ($courseSet['title'] == $oldCourseSet['title']) {
            return;
        }
        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
        $agentConfigs = $this->getAgentConfigService()->findAgentConfigsByCourseIds(array_column($courses, 'id'));
        foreach ($agentConfigs as $agentConfig) {
            $this->getAIService()->updateDataset($agentConfig['datasetId'], ['name' => $courseSet['title']]);
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
        $this->getAIService()->deleteDocument($activity['documentId']);
        $this->createDatasetDocumentIfNecessary($agentConfig['datasetId'], $activity);
    }

    public function onActivityDelete(Event $event)
    {
        $activity = $event->getSubject();
        if (!empty($activity['documentId'])) {
            $this->getAIService()->deleteDocument($activity['documentId']);
        }
    }

    private function createDatasetDocumentIfNecessary($datasetId, $activity)
    {
        if ('text' == $activity['mediaType']) {
            $document = $this->getAIService()->createDocumentByText([
                'datasetId' => $datasetId,
                'extId' => $activity['id'],
                'name' => $activity['title'],
                'content' => $activity['content'],
            ]);
        }
        if (in_array($activity['mediaType'], ['audio', 'doc', 'ppt', 'video'])) {
            $activity = $this->getActivityService()->getActivity($activity['id'], true);
            $document = $this->getAIService()->createDocumentByResource([
                'datasetId' => $datasetId,
                'extId' => $activity['id'],
                'name' => $activity['title'],
                'resNo' => $activity['ext']['file']['globalId'],
            ]);
        }
        if (!empty($document)) {
            $this->getActivityService()->updateActivity($activity['id'], ['documentId' => $document['id']]);
        }
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
