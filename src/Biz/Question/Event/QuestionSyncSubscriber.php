<?php

namespace Biz\Question\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\File\Service\UploadFileService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Activity\Service\ActivityService;
use Biz\Question\Service\QuestionService;
use Biz\Course\Event\CourseSyncSubscriber;

class QuestionSyncSubscriber extends CourseSyncSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'question.create' => array('onQuestionCreate', 1),
            'question.update' => array('onQuestionUpdate', 1),
            'question.delete' => array('onQuestionDelete', 1),
        );
    }

    public function onQuestionCreate(Event $event)
    {
        $question = $event->getSubject();
        if ($question['copyId'] > 0) {
            return;
        }

        $copiedCourses = $this->findLockedCourseSetsWithCourses($question['courseSetId']);
        if (empty($copiedCourses)) {
            return;
        }

        $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $parentTasks = $this->findTasksByCopyIdAndCourseIds($question['lessonId'], $copiedCourseIds);
        $parentTasks = ArrayToolkit::index($parentTasks, 'courseId');

        $parentQuestions = array();
        if ($question['parentId'] > 0) {
            $parentQuestions = $this->findParentQuestionsByCopyId($question['parentId']);
        }

        $biz = $this->getBiz();
        $user = $biz['user'];
        //create questions
        $newQuestions = array();
        foreach ($copiedCourses as $courseSetId => $copiedCourse) {
            $fields = array(
                'type',
                'stem',
                'score',
                'answer',
                'analysis',
                'metas',
                'categoryId',
                'difficulty',
                'target',
                'subCount',
            );
            $newQuestion = ArrayToolkit::parts($question, $fields);
            $newQuestion['copyId'] = $question['id'];
            $newQuestion['courseSetId'] = $courseSetId;
            $newQuestion['courseId'] = $question['courseId'] > 0 ? $copiedCourse['id'] : 0;

            $newQuestion['lessonId'] = empty($parentTasks[$copiedCourse['id']]) ? 0 : $parentTasks[$copiedCourse['id']]['id'];
            $newQuestion['parentId'] = $question['parentId'] > 0 ? $parentQuestions[$courseSetId]['id'] : 0;
            $newQuestion['createdUserId'] = $user['id'];
            $newQuestion['updatedUserId'] = $user['id'];

            $newQuestions[] = $newQuestion;
        }

        $this->getQuestionService()->batchCreateQuestions($newQuestions);

        $courseSetIds = array_keys($copiedCourses);
        $this->createQuestionAttachments($question, $courseSetIds);

        if ($question['parentId'] > 0) {
            $parent = $this->getQuestionService()->get($question['parentId']);
            $parentQuestions = $this->getQuestionService()->updateCopyQuestionsSubCount($parent['id'], $parent['subCount']);
        }
    }

    public function onQuestionUpdate(Event $event)
    {
        $question = $event->getSubject();
        if ($question['copyId'] > 0) {
            return;
        }

        $copiedCourses = $this->findLockedCourseSetsWithCourses($question['courseSetId']);
        if (empty($copiedCourses)) {
            return;
        }

        $courseSetIds = array_keys($copiedCourses);
        $copiedQuestions = $this->getQuestionService()->search(
            array('copyId' => $question['id'], 'courseSetIds' => $courseSetIds),
            array(),
            0,
            PHP_INT_MAX
        );
        if (empty($copiedQuestions)) {
            return;
        }

        $copiedCourseIds = ArrayToolkit::column($copiedCourses, 'id');
        $parentTasks = $this->findTasksByCopyIdAndCourseIds($question['lessonId'], $copiedCourseIds);
        $parentTasks = ArrayToolkit::index($parentTasks, 'fromCourseSetId');

        foreach ($copiedQuestions as $cc) {
            $fields = array(
                'type',
                'stem',
                'score',
                'answer',
                'analysis',
                'metas',
                'categoryId',
                'difficulty',
                'target',
                'subCount',
            );
            $updatedFields = ArrayToolkit::parts($question, $fields);

            $updatedFields['courseId'] = empty($question['courseId']) ? 0 : $copiedCourses[$cc['courseSetId']]['id'];
            $updatedFields['lessonId'] = empty($parentTasks[$cc['courseSetId']]['id']) ? 0 : $parentTasks[$cc['courseSetId']]['id'];
            $this->getQuestionService()->update($cc['id'], $updatedFields);
            //file_used
            $this->updateQuestionAttachments($cc, $question);
        }
    }

    public function onQuestionDelete(Event $event)
    {
        $question = $event->getSubject();
        if ($question['copyId'] > 0) {
            return;
        }
        $courseSetIds = $this->findLockedCourseSetIds($question['courseSetId']);
        if (empty($courseSetIds)) {
            return;
        }
        $copiedQuestions = $this->getQuestionService()->search(
            array('copyId' => $question['id'], 'courseSetIds' => $courseSetIds),
            array(),
            0,
            PHP_INT_MAX
        );
        if (empty($copiedQuestions)) {
            return;
        }

        foreach ($copiedQuestions as $cc) {
            $files = $this->getUploadFileService()->searchUseFiles(
                array('targetTypes' => array('question.stem', 'question.analysis'), 'targetId' => $cc['id'])
            );
            if (!empty($files)) {
                $fileIds = ArrayToolkit::column($files, 'id');
                foreach ($fileIds as $fid) {
                    $this->getUploadFileService()->deleteUseFile($fid);
                }
            }
            $this->getQuestionService()->delete($cc['id']);
        }
    }

    protected function createQuestionAttachments($question, $copiedCourseSetIds)
    {
        $conditions = array(
            'copyId' => $question['id'],
            'courseSetIds' => $copiedCourseSetIds,
        );
        $copiedQuestions = $this->getQuestionService()->search($conditions, array(), 0, PHP_INT_MAX);

        $conditions = array(
            'targetId' => $question['id'],
            'targetTypes' => array('question.stem', 'question.analysis'),
            'type' => 'attachment',
        );
        $attachments = $this->getUploadFileService()->searchUseFiles($conditions, $bindFile = false);

        $newAttachments = array();
        foreach ($copiedQuestions as $copyQuestion) {
            foreach ($attachments as $attachment) {
                $attachment = array(
                    'fileId' => $attachment['fileId'],
                    'targetType' => $attachment['targetType'],
                    'targetId' => $copyQuestion['id'],
                    'type' => 'attachment',
                    'createdTime' => time(),
                );

                $newAttachments[] = $attachment;
            }
        }

        $this->getUploadFileService()->batchCreateUseFiles($newAttachments);
    }

    protected function updateQuestionAttachments($copiedQuestion, $sourceQuestion)
    {
        $stems = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType('question.stem', $sourceQuestion['id'], 'attachment');
        if (!empty($stems)) {
            $fileIds = ArrayToolkit::column($stems, 'fileId');
            $this->getUploadFileService()->createUseFiles($fileIds, $copiedQuestion['id'], 'question.stem', 'attachment');
        }
        $analysises = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType('question.analysis', $sourceQuestion['id'], 'attachment');
        if (!empty($analysises)) {
            $fileIds = ArrayToolkit::column($analysises, 'fileId');
            $this->getUploadFileService()->createUseFiles($fileIds, $copiedQuestion['id'], 'question.analysis', 'attachment');
        }
    }

    private function findLockedCourseSetIds($courseSetId)
    {
        $copiedCourseSets = $this->getCourseSetDao()->findCourseSetsByParentIdAndLocked($courseSetId, 1);
        if (empty($copiedCourseSets)) {
            return null;
        }

        return ArrayToolkit::column($copiedCourseSets, 'id');
    }

    private function findLockedCourseSetsWithCourses($courseSetId)
    {
        $copiedCourseSets = $this->getCourseSetDao()->findCourseSetsByParentIdAndLocked($courseSetId, 1);
        if (empty($copiedCourseSets)) {
            return null;
        }
        $courseSetIds = ArrayToolkit::column($copiedCourseSets, 'id');
        $copiedCourses = $this->getCourseService()->findCoursesByCourseSetIds($courseSetIds);

        return ArrayToolkit::index($copiedCourses, 'courseSetId');
    }

    protected function findTasksByCopyIdAndCourseIds($taskId, $copiedCourseIds)
    {
        if (empty($taskId)) {
            return array();
        }

        $conditions = array(
            'copyId' => $taskId,
            'courseIds' => $copiedCourseIds,
        );
        $tasks = $this->getTaskService()->searchTasks($conditions, array(), 0, PHP_INT_MAX);

        if (empty($tasks)) {
            return array();
        }

        return $tasks;
    }

    protected function findParentQuestionsByCopyId($questionId)
    {
        $conditions = array(
            'copyId' => $questionId,
        );

        $questions = $this->getQuestionService()->search($conditions, array(), 0, PHP_INT_MAX);

        return ArrayToolkit::index($questions, 'courseSetId');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->getBiz()->service('Question:QuestionService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }
}
