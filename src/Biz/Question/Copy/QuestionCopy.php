<?php

namespace Biz\Question\Copy;

use Biz\AbstractCopy;
use AppBundle\Common\ArrayToolkit;
use Biz\File\Service\UploadFileService;
use Biz\Question\Dao\QuestionDao;

class QuestionCopy extends AbstractCopy
{
    public function doCopy($source, $options)
    {
        $newCourseSet = $options['newCourseSet'];
        $this->cloneParentQuestions($source, $newCourseSet);
        $this->cloneChildrenQuestions($source, $newCourseSet);
        $questions = $this->getQuestionDao()->findQuestionsByCourseSetId($newCourseSet['id']);
        $questions = ArrayToolkit::index($questions, 'copyId');

        $this->copyAttachments($questions);
    }

    protected function getFields()
    {
        return array(
            'type',
            'stem',
            'score',
            'answer',
            'analysis',
            'metas',
            'categoryId',
            'courseId', //先保存courseId LessonId 后面再更新
            'lessonId',
            'difficulty',
            'subCount',
        );
    }

    public function preCopy($source, $options)
    {
    }

    private function cloneParentQuestions($originalCourseSet, $newCourseSet)
    {
        $conditions = array(
            'parentId' => 0,
            'courseSetId' => $originalCourseSet['id'],
        );
        $parentQuestions = $this->getQuestionDao()->search($conditions, array(), 0, PHP_INT_MAX);

        if (empty($parentQuestions)) {
            return;
        }
        $newQuestions = array();
        $userId = $this->biz['user']->getId();

        foreach ($parentQuestions as $question) {
            $newQuestion = $this->partsFields($question);
            $newQuestion['courseSetId'] = $newCourseSet['id'];
            $newQuestion['copyId'] = $question['id']; //暂时存储copyId，当把childrenQuestion 填写之后，再Update将copyId归零
            $newQuestion['createdUserId'] = $userId;
            $newQuestion['updatedUserId'] = $userId;
            $newQuestion['parentId'] = 0;

            $newQuestions[] = $newQuestion;
        }
        if (!empty($newQuestions)) {
            $this->getQuestionDao()->batchCreate($newQuestions);
        }
    }

    private function cloneChildrenQuestions($originalCourseSet, $newCourseSet)
    {
        $newQuestions = $this->getQuestionDao()->findQuestionsByCourseSetId($newCourseSet['id']);
        $newQuestions = ArrayToolkit::index($newQuestions, 'copyId');

        $conditions = array(
            'parentIdGT' => 0,
            'courseSetId' => $originalCourseSet['id'],
        );
        $childrenQuestions = $this->getQuestionDao()->search($conditions, array(), 0, PHP_INT_MAX);
        if (empty($childrenQuestions)) {
            return;
        }
        $newChildQuestions = array();
        $userId = $this->biz['user']->getId();

        foreach ($childrenQuestions as $question) {
            $newQuestion = $this->partsFields($question);
            $newQuestion['courseSetId'] = $newCourseSet['id'];
            $newQuestion['createdUserId'] = $userId;
            $newQuestion['updatedUserId'] = $userId;
            $newQuestion['copyId'] = $question['id'];
            $parentQuestion = $newQuestions[$question['parentId']];
            $newQuestion['parentId'] = $parentQuestion['id'];

            $newChildQuestions[] = $newQuestion;
        }

        $this->getQuestionDao()->batchCreate($newChildQuestions);
    }

    private function copyAttachments($questionMaps)
    {
        if (empty($questionMaps)) {
            return;
        }

        $targetIds = array_keys($questionMaps);
        $conditions = array(
            'type' => 'attachment',
            'targetTypes' => array('question.stem', 'question.analysis'),
            'targetIds' => $targetIds,
        );
        $attachments = $this->getUploadFileService()->searchUseFiles($conditions, false);

        $newAttachments = array();
        foreach ($attachments as $attachment) {
            $newTargetId = $questionMaps[$attachment['targetId']]['id'];
            $newAttachment = array(
                'type' => 'attachment',
                'fileId' => $attachment['fileId'],
                'targetType' => $attachment['targetType'],
                'targetId' => $newTargetId,
            );

            $newAttachments[] = $newAttachment;
        }

        $this->getUploadFileService()->batchCreateUseFiles($newAttachments);
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('Question:QuestionDao');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }
}
