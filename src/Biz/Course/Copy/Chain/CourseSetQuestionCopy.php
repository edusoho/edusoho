<?php

namespace Biz\Course\Copy\Chain;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\File\Service\UploadFileService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;

class CourseSetQuestionCopy extends AbstractEntityCopy
{
    /**
     * 复制链说明：
     * CourseSet
     * - Question
     *   - Attachment 问题附件.
     * 由于exercise类型任务的题目列表是使用时自动创建的，因此需要把题目事先复制过去.
     *
     * @param
     */
    protected function copyEntity($source, $config = array())
    {
        $newCourse = $config['newCourse'];

        return $this->doCopyQuestions($newCourse, $source);
    }

    /*
     * $ids = question ids
     * */
    protected function doCopyQuestions($newCourse, $sourceCourse)
    {
        $this->copyParentQuestions($newCourse, $sourceCourse['courseSetId']);
        $this->copyChildrenQuestions($newCourse, $sourceCourse['courseSetId']);

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($newCourse['courseSetId']);
        $questions = ArrayToolkit::index($questions, 'copyId');

        $this->copyAttachments($questions);

        return $questions;
    }

    protected function copyParentQuestions($newCourse, $sourceCourseSetId)
    {
        $conditions = array(
            'parentId' => 0,
            'courseSetId' => $sourceCourseSetId,
        );
        $parentQuestions = $this->getQuestionService()->search($conditions, array(), 0, PHP_INT_MAX);

        if (empty($parentQuestions)) {
            return;
        }

        $newQuestions = array();
        foreach ($parentQuestions as $question) {
            $newQuestion = $this->processFields($newCourse, $question);
            $newQuestion['parentId'] = 0;

            $newQuestions[] = $newQuestion;
        }

        $this->getQuestionService()->batchCreateQuestions($newQuestions);
    }

    protected function copyChildrenQuestions($newCourse, $sourceCourseSetId)
    {
        $newQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($newCourse['courseSetId']);
        $newQuestions = ArrayToolkit::index($newQuestions, 'copyId');

        $conditions = array(
            'parentIdGT' => 0,
            'courseSetId' => $sourceCourseSetId,
        );
        $childrenQuestions = $this->getQuestionService()->search($conditions, array(), 0, PHP_INT_MAX);

        if (empty($childrenQuestions)) {
            return;
        }

        $newChildQuestions = array();
        foreach ($childrenQuestions as $question) {
            $newQuestion = $this->processFields($newCourse, $question);
            $parentQuestion = $newQuestions[$question['parentId']];
            $newQuestion['parentId'] = $parentQuestion['id'];

            $newChildQuestions[] = $newQuestion;
        }

        $this->getQuestionService()->batchCreateQuestions($newChildQuestions);
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

    private function questionSort($questions)
    {
        usort($questions, function ($a, $b) {
            if ($a['parentId'] == $b['parentId']) {
                return 0;
            }

            return $a['parentId'] < $b['parentId'] ? -1 : 1;
        });

        return $questions;
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
            'difficulty',
            'subCount',
        );
    }

    private function processFields($newCourse, $question)
    {
        $newQuestion = $this->filterFields($question);

        if ($question['courseId'] > 0) {
            $newQuestion['courseId'] = $newCourse['id'];
        } else {
            $newQuestion['courseId'] = 0;
        }
        $newQuestion['courseSetId'] = $newCourse['courseSetId'];

        //lessonId为taskId,先赋值老的lessonId,后面复制好task后再做修改
        $newQuestion['lessonId'] = $question['lessonId'];
        $newQuestion['copyId'] = $question['id'];
        $newQuestion['createdUserId'] = $this->biz['user']['id'];
        $newQuestion['updatedUserId'] = $this->biz['user']['id'];

        return $newQuestion;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }
}
