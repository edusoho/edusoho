<?php

namespace Biz\S2B2C\Sync\Component;

use AppBundle\Common\ArrayToolkit;
use Biz\File\Dao\FileUsedDao;
use Biz\File\Service\UploadFileService;
use Biz\Question\Dao\QuestionDao;
use Biz\Question\Service\QuestionService;
use Biz\Testpaper\Service\TestpaperService;

class CourseSetQuestionSync extends AbstractEntitySync
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
    protected function syncEntity($source, $config = array())
    {
        $newCourse = $config['newCourse'];

        return $this->doSyncQuestions($newCourse, $source);
    }

    /*
     * $ids = question ids
     * */
    protected function doSyncQuestions($newCourse, $sourceCourse)
    {
        $this->syncParentQuestions($newCourse, $sourceCourse);
        $this->syncChildrenQuestions($newCourse, $sourceCourse);

        $syncIds = array_merge(
            ArrayToolkit::column($sourceCourse['childrenQuestions'], 'id'),
            ArrayToolkit::column($sourceCourse['parentQuestions'], 'id')
        );
        $questions = $this->getQuestionService()->findQuestionsBySyncIds($syncIds);

        $questions = ArrayToolkit::index($questions, 'syncId');
        $this->syncAttachments($questions, $newCourse, $sourceCourse);

        return $questions;
    }

    protected function syncParentQuestions($newCourse, $sourceCourse)
    {
        $parentQuestions = $sourceCourse['parentQuestions'];

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

    protected function syncChildrenQuestions($newCourse, $sourceCourse)
    {
        $childrenQuestions = $sourceCourse['childrenQuestions'];
        if (empty($childrenQuestions)) {
            return;
        }

        $newQuestions = $this->getQuestionService()->findQuestionsBySyncIds(ArrayToolkit::column($sourceCourse['childrenQuestions'], 'parentId'));

        $newChildQuestions = array();
        foreach ($childrenQuestions as $question) {
            $newQuestion = $this->processFields($newCourse, $question);
            $parentQuestion = $newQuestions[$question['parentId']];
            $newQuestion['parentId'] = $parentQuestion['id'];
            $newChildQuestions[] = $newQuestion;
        }

        $this->getQuestionService()->batchCreateQuestions($newChildQuestions);
    }

    private function syncAttachments($questionMaps, $newCourse, $sourceCourse)
    {
        $attachments = $sourceCourse['questionAttachments'];
        if (empty($attachments)) {
            return;
        }
        $files = $this->getUploadFileService()->searchFiles(array('targetId' => $newCourse['courseSetId']), array(), 0, PHP_INT_MAX);
        $files = ArrayToolkit::index($files, 'syncId');

        $newAttachments = array();
        foreach ($attachments as $attachment) {
            $newTargetId = empty($questionMaps[$attachment['targetId']]) ? 0 : $questionMaps[$attachment['targetId']]['id'];
            $newAttachment = array(
                'syncId' => $attachment['id'],
                'type' => 'attachment',
                'fileId' => empty($files[$attachment['fileId']]) ? 0 : $files[$attachment['fileId']]['id'],
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
            'bankId',
        );
    }

    private function processFields($newCourse, $question)
    {
        $newQuestion = $this->filterFields($question);
        $newQuestion['courseId'] = 0;
        $newQuestion['courseSetId'] = 0;
        $newQuestion['lessonId'] = 0;
        $newQuestion['copyId'] = 0;
        $newQuestion['syncId'] = $question['id'];
        $newQuestion['createdUserId'] = $this->biz['user']['id'];
        $newQuestion['updatedUserId'] = $this->biz['user']['id'];

        return $newQuestion;
    }

    protected function updateEntityToLastedVersion($source, $config = array())
    {
        $newCourse = $config['newCourse'];
        $this->updateParentQuestions($newCourse, $source);
        $this->updateChildrenQuestions($newCourse, $source);

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($newCourse['courseSetId']);
        $questions = ArrayToolkit::index($questions, 'syncId');

        $this->updateAttachments($questions, $newCourse, $source);

        return $questions;
    }

    protected function updateParentQuestions($newCourse, $sourceCourse)
    {
        $parentQuestions = $sourceCourse['parentQuestions'];
        $existParentQuestions = $this->getQuestionService()->search(array('courseSetId' => $newCourse['courseSetId'], 'parentId' => 0), array(), 0, PHP_INT_MAX);

        if (empty($parentQuestions)) {
            foreach ($existParentQuestions as $existParentQuestion) {
                $this->getQuestionService()->delete($existParentQuestion['id']);
            }

            return;
        }

        $newQuestions = array();
        $existParentQuestions = ArrayToolkit::index($existParentQuestions, 'syncId');
        foreach ($parentQuestions as $question) {
            $newQuestion = $this->processFields($newCourse, $question);
            if (!empty($existParentQuestions[$question['id']])) {
                $this->getQuestionDao()->update($existParentQuestions[$question['id']]['id'], $newQuestion);
                continue;
            }
            $newQuestion['parentId'] = 0;

            $newQuestions[] = $newQuestion;
        }

        $this->getQuestionService()->batchCreateQuestions($newQuestions);

        $needDeleteParentQuestionSyncIds = array_values(array_diff(array_keys($existParentQuestions), ArrayToolKit::column($parentQuestions, 'id')));
        if (!empty($existParentQuestions) && !empty($needDeleteParentQuestionSyncIds)) {
            $needDeleteParentQuestions = $this->getQuestionDao()->search(array('parentId' => 0, 'courseSetId' => $newCourse['courseSetId'], 'syncIds' => $needDeleteParentQuestionSyncIds), array(), 0, PHP_INT_MAX);
            foreach ($needDeleteParentQuestions as $needDeleteParentQuestion) {
                $this->getQuestionDao()->delete($needDeleteParentQuestion['id']);
            }
        }
    }

    protected function updateChildrenQuestions($newCourse, $sourceCourse)
    {
        $childrenQuestions = $sourceCourse['childrenQuestions'];
        $existChildrenQuestions = $this->getQuestionService()->search(array('courseSetId' => $newCourse['courseSetId'], 'parentIdGT' => 0), array(), 0, PHP_INT_MAX);
        if (empty($childrenQuestions)) {
            foreach ($existChildrenQuestions as $existChildrenQuestion) {
                $this->getQuestionService()->delete($existChildrenQuestion['id']);
            }

            return;
        }

        $newQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($newCourse['courseSetId']);
        $newQuestions = ArrayToolkit::index($newQuestions, 'syncId');
        $existChildrenQuestions = ArrayToolkit::index($existChildrenQuestions, 'syncId');

        $newChildQuestions = array();
        foreach ($childrenQuestions as $question) {
            $newQuestion = $this->processFields($newCourse, $question);
            $parentQuestion = $newQuestions[$question['parentId']];
            $newQuestion['parentId'] = $parentQuestion['id'];
            if (!empty($existChildrenQuestions[$question['id']])) {
                $this->getQuestionDao()->update($existChildrenQuestions[$question['id']]['id'], $newQuestion);
                continue;
            }

            $newChildQuestions[] = $newQuestion;
        }

        $this->getQuestionService()->batchCreateQuestions($newChildQuestions);

        $needDeleteChildrenQuestionSyncIds = array_values(array_diff(array_keys($existChildrenQuestions), ArrayToolKit::column($childrenQuestions, 'id')));
        if (!empty($existChildrenQuestions) && !empty($needDeleteChildrenQuestionSyncIds)) {
            $needDeleteChildrenQuestions = $this->getQuestionDao()->search(array('parentIdGT' => 0, 'courseSetId' => $newCourse['courseSetId'], 'syncIds' => $needDeleteChildrenQuestionSyncIds), array(), 0, PHP_INT_MAX);
            foreach ($needDeleteChildrenQuestions as $needDeleteChildrenQuestion) {
                $this->getQuestionDao()->delete($needDeleteChildrenQuestion['id']);
            }
        }
    }

    private function updateAttachments($questionMaps, $newCourse, $sourceCourse)
    {
        $attachments = $sourceCourse['questionAttachments'];
        $files = $this->getUploadFileService()->searchFiles(array('targetId' => $newCourse['courseSetId']), array(), 0, PHP_INT_MAX);
        $files = ArrayToolkit::index($files, 'syncId');
        $existUsedFiles = $this->getFileUsedDao()->search(array(
            'type' => 'attachment',
            'fileIds' => ArrayToolkit::column($files, 'id'),
            'syncIds' => ArrayToolkit::column($files, 'syncId'),
        ), array(), 0, PHP_INT_MAX);

        if (empty($attachments)) {
            foreach ($existUsedFiles as $existUsedFile) {
                $this->getFileUsedDao()->delete($existUsedFile['id']);
            }

            return;
        }

        $newAttachments = array();
        $existUsedFiles = ArrayToolkit::index($existUsedFiles, 'syncId');
        foreach ($attachments as $attachment) {
            $newTargetId = empty($questionMaps[$attachment['targetId']]) ? 0 : $questionMaps[$attachment['targetId']]['id'];
            $newAttachment = array(
                'syncId' => $attachment['id'],
                'type' => 'attachment',
                'fileId' => empty($files[$attachment['fileId']]) ? 0 : $files[$attachment['fileId']]['id'],
                'targetType' => $attachment['targetType'],
                'targetId' => $newTargetId,
            );
            if (!empty($existUsedFiles[$attachment['id']])) {
                $this->getFileUsedDao()->update($existUsedFiles[$attachment['id']]['id'], $newAttachment);
                continue;
            }

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

    /**
     * @return FileUsedDao
     */
    protected function getFileUsedDao()
    {
        return $this->biz->dao('File:FileUsedDao');
    }
}
