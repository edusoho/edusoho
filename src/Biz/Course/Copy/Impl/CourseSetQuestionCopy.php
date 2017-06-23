<?php

namespace Biz\Course\Copy\Impl;

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
     * @param $biz
     */
    public function __construct($biz, $node)
    {
        parent::__construct($biz, $node);
    }

    protected function copyEntity($source, $config = array())
    {
        $newCourse = $config['newCourse'];

        return $this->doCopyQuestions($newCourse, $source, $config['isCopy']);
    }

    /*
     * $ids = question ids
     * */
    protected function doCopyQuestions($newCourse, $sourceCourse, $isCopy)
    {
        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($sourceCourse['courseSetId']);
        $questions = $this->questionSort($questions);

        $questionMap = array();
        foreach ($questions as $question) {
            $newQuestion = $this->filterFields($newCourse, $question, $isCopy);

            $newQuestion['parentId'] = $question['parentId'] > 0 ? $questionMap[$question['parentId']][0] : 0;

            $newQuestion = $this->getQuestionService()->create($newQuestion);
            $this->copyAttachments($newQuestion, $question);

            $questionMap[$question['id']] = array($newQuestion['id'], $newQuestion['parentId']);
        }

        return $questionMap;
    }

    private function copyAttachments($newQuestion, $sourceQuestion)
    {
        $stems = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType(
            'question.stem',
            $sourceQuestion['id'],
            'attachment'
        );
        if (!empty($stems)) {
            $fileIds = ArrayToolkit::column($stems, 'fileId');
            $this->getUploadFileService()->createUseFiles(
                $fileIds,
                $newQuestion['id'],
                'question.stem',
                'attachment'
            );
        }
        $analysises = $this->getUploadFileService()->findUseFilesByTargetTypeAndTargetIdAndType(
            'question.analysis',
            $sourceQuestion['id'],
            'attachment'
        );
        if (!empty($analysises)) {
            $fileIds = ArrayToolkit::column($analysises, 'fileId');
            $this->getUploadFileService()->createUseFiles(
                $fileIds,
                $newQuestion['id'],
                'question.analysis',
                'attachment'
            );
        }
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
        );
    }

    private function filterFields($newCourse, $question, $isCopy)
    {
        $fields = $this->getFields();

        $newQuestion = ArrayToolkit::parts($question, $fields);
        if ($question['courseId'] > 0) {
            $newQuestion['courseId'] = $newCourse['id'];
        } else {
            $newQuestion['courseId'] = 0;
        }
        $newQuestion['courseSetId'] = $newCourse['courseSetId'];
        //lessonId怎么从旧的taskId赋值为新的taskId
        $newQuestion['lessonId'] = 0;
        $newQuestion['copyId'] = $isCopy ? $question['id'] : 0;
        $newQuestion['createdUserId'] = $this->biz['user']['id'];

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
