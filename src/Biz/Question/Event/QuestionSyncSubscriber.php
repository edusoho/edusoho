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
            // 'question.create' => 'onQuestionCreate',
            'question.update' => 'onQuestionUpdate',
            'question.delete' => 'onQuestionDelete',
        );
    }

    public function onQuestionUpdate(Event $event)
    {
        $question = $event->getSubject();
        if ($question['copyId'] > 0) {
            return;
        }
        $copiedCourses = $this->getCourseService()->findCoursesByParentIdAndLocked($question['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $courseSetIds = ArrayToolkit::column($copiedCourses, 'courseSetId');
        $copiedQuestions = $this->getQuestionService()->search(array('copyId' => $question['id'], 'courseSetIds' => $courseSetIds), array(), 0, PHP_INT_MAX);
        if (empty($copiedQuestions)) {
            return;
        }

        foreach ($copiedQuestions as $cc) {
            $cc = $this->copyFields($question, $cc, array(
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
            ));

            if ($question['lessonId'] > 0) {
                $activity = $this->getActivityService()->getActivityByCopyIdAndCourseSetId($question['lessonId'], $cc['courseSetId']);
                if (!empty($activity)) {
                    $cc['lessonId'] = $activity['id'];
                }
            }
            $this->getQuestionService()->update($cc['id'], $cc);
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
        $copiedCourses = $this->getCourseService()->findCoursesByParentIdAndLocked($question['courseId'], 1);
        if (empty($copiedCourses)) {
            return;
        }
        $courseSetIds = ArrayToolkit::column($copiedCourses, 'courseSetId');
        $copiedQuestions = $this->getQuestionService()->search(array('copyId' => $question['id'], 'courseSetIds' => $courseSetIds), array(), 0, PHP_INT_MAX);
        if (empty($copiedQuestions)) {
            return;
        }

        foreach ($copiedQuestions as $cc) {
            $files = $this->getUploadFileService()->searchUseFiles(array('targetTypes' => array('question.stem', 'question.analysis'), 'targetId' => $cc['id']));
            if (!empty($files)) {
                $fileIds = ArrayToolkit::column($files, 'id');
                foreach ($fileIds as $fid) {
                    $this->getUploadFileService()->deleteUseFile($fid);
                }
            }
            $this->getQuestionService()->delete($cc['id']);
        }
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

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
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
