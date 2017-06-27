<?php

namespace Biz\Course\Copy\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Copy\AbstractEntityCopy;
use Biz\File\Service\UploadFileService;
use Biz\Activity\Service\ActivityService;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Activity\Service\TestpaperActivityService;

/**
 * Class QuestionCopy.
 *
 * @deprecated
 * @see TestpaperCopy
 */
class QuestionCopy extends AbstractEntityCopy
{
    /**
     * 复制链说明：
     * Question
     * - Question attachment 问题附件.
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
        $courseId = $sourceCourse['id'];
        $newCourseSetId = $newCourse['courseSetId'];
        $testpapers = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'testpaper');
        $others = $this->getActivityService()->search(
            array('fromCourseId' => $courseId, 'mediaTypes' => array('homework', 'exercise')),
            array(),
            0,
            PHP_INT_MAX
        );

        $testpaperExt = $this->getTestpaperActivityService()->findActivitiesByIds(ArrayToolkit::column($testpapers, 'mediaId'));

        $testpaperIds = array_merge(ArrayToolkit::column($testpaperExt, 'mediaId'), ArrayToolkit::column($others, 'mediaId'));
        if (empty($testpaperIds)) {
            return array();
        }

        $testpaperItems = $this->getTestpaperService()->findItemsByTestIds($testpaperIds);
        $questionIds = ArrayToolkit::column($testpaperItems, 'questionId');
        if (empty($questionIds)) {
            return array();
        }

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);
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
            $this->getUploadFileService()->createUseFiles($fileIds, $newQuestion['id'], 'question.stem', 'attachment');
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

    private function filterFields($newCourse, $question, $isCopy)
    {
        $fields = array(
            'type',
            'stem',
            'score',
            'answer',
            'analysis',
            'metas',
            'categoryId',
            'difficulty',
        );

        $newQuestion = ArrayToolkit::parts($question, $fields);
        if ($question['courseId'] > 0) {
            $newQuestion['courseId'] = $newCourse['id'];
        } else {
            $newQuestion['courseId'] = 0;
        }
        $newQuestion['courseSetId'] = $newCourse['courseSetId'];
        $newQuestion['lessonId'] = 0;
        $newQuestion['copyId'] = $isCopy ? $question['id'] : 0;
        $newQuestion['createdUserId'] = $this->biz['user']['id'];
        $newQuestion['target'] = 'course-'.$newCourse['id'];

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
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
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
