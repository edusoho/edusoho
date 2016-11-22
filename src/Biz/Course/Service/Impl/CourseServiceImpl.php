<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Topxia\Common\ArrayToolkit;

class CourseServiceImpl extends BaseService implements CourseService
{
    public function getCourseItems($courseId)
    {
        $items = array();
        $user = $this->getCurrentUser();
        $tasks = $this->getTaskService()->findTasksWithLearningResultByCourseId($courseId);
        foreach ($tasks as $task) {
            $task['itemType']              = 'task';
            $items["task-{$task['id']}"] = $task;
        }

        $chapters = $this->getChapterDao()->findChaptersByCourseId($courseId);
        foreach ($chapters as $chapter) {
            $chapter['itemType']               = 'chapter';
            $items["chapter-{$chapter['id']}"] = $chapter;
        }

        uasort($items, function ($item1, $item2) {
            return $item1['seq'] > $item2['seq'];
        });

        return $items;
    }

    public function tryManageCourse($courseId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createServiceException($this->getKernel()->trans('未登录用户，无权操作！'));
        }

        $course = $this->getCourseDao()->get($courseId);

        if (empty($course)) {
            throw $this->createServiceException();
        }

        if (!$this->hasCourseManagerRole($courseId, $user['id'])) {
            throw $this->createServiceException($this->getKernel()->trans('您不是课程的教师或管理员，无权操作！'));
        }

        return $course;
    }

    public function sortCourseItems($courseId, $ids)
    {
        $this->tryManageCourse($courseId);
        $parentChapterId = 0;
        foreach ($ids as $key => $id) {
            if(strpos($id, 'chapter') === 0) {
                $id = str_replace('chapter-', '', $id);
                $fileds = array('seq' => $key);
                $chapter = $this->getChapterDao()->get($id);
                if($chapter['type'] != 'chapter'){
                    $fileds['parentId'] = $parentChapterId;
                } else {
                    $parentChapterId = $id;
                }
                $this->getChapterDao()->update($id, $fileds);
            }

            if(strpos($id, 'task') === 0) {
                $id = str_replace('task-', '', $id);
                $this->getTaskService()->updateSeq($id, array('seq' => $key, 'courseChapterId' => $parentChapterId));    
            }
        }

    }

    public function createChapter($chapter)
    {
        $argument = $chapter;

        if (!in_array($chapter['type'], array('chapter', 'unit', 'lesson'))) {
            throw $this->createServiceException($this->getKernel()->trans('章节类型不正确，添加失败！'));
        }

        if (in_array($chapter['type'], array('unit', 'lesson'))) {
            list($chapter['number'], $chapter['parentId']) = $this->getNextNumberAndParentId($chapter['courseId']);
        } else {
            $chapter['number']   = $this->getNextChapterNumber($chapter['courseId']);
            $chapter['parentId'] = 0;
        }

        $chapter['seq']         = $this->getNextCourseItemSeq($chapter['courseId']);
        $chapter['createdTime'] = time();
        $chapter                = $this->getChapterDao()->create($chapter);
        return $chapter;
    }

    protected function getNextNumberAndParentId($courseId)
    {
        $lastChapter = $this->getChapterDao()->getLastChapterByCourseIdAndType($courseId, 'chapter');

        $parentId = empty($lastChapter) ? 0 : $lastChapter['id'];

        $num = 1 + $this->getChapterDao()->getChapterCountByCourseIdAndTypeAndParentId($courseId, 'unit', $parentId);

        return array($num, $parentId);
    }

    protected function getNextChapterNumber($courseId)
    {
        $counter = $this->getChapterDao()->getChapterCountByCourseIdAndType($courseId, 'chapter');
        return $counter + 1;
    }

    protected function getNextCourseItemSeq($courseId)
    {
        $chapterMaxSeq = $this->getChapterDao()->getChapterMaxSeqByCourseId($courseId);
        $taskMaxSeq  = $this->getTaskService()->getMaxSeqByCourseId($courseId);
        return ($chapterMaxSeq > $taskMaxSeq ? $chapterMaxSeq : $taskMaxSeq) + 1;
    }

    public function updateChapter($courseId, $chapterId, $fields)
    {
        $argument = $fields;
        $course = $this->tryManageCourse($courseId);
        $chapter  = $this->getChapterDao()->get($chapterId);

        if (empty($chapter) || $chapter['courseId'] != $courseId) {
            throw $this->createServiceException($this->getKernel()->trans('章节#%chapterId%不存在！', array('%chapterId%' => $chapterId)));
        }

        $fields  = ArrayToolkit::parts($fields, array('title', 'number', 'seq', 'parentId'));
        $chapter = $this->getChapterDao()->update($chapterId, $fields);

        return $chapter;
    }

    public function deleteChapter($courseId, $chapterId)
    {
        $course = $this->tryManageCourse($courseId);

        $deletedChapter = $this->getChapterDao()->get($chapterId);

        if (empty($deletedChapter) || $deletedChapter['courseId'] != $courseId) {
            throw $this->createServiceException(sprintf($this->getKernel()->trans('章节(ID:%s)不存在，删除失败！'), $chapterId));
        }

        $this->getChapterDao()->delete($deletedChapter['id']);

        $prevChapter = array('id' => 0);

        foreach ($this->getChapterDao()->findChaptersByCourseId($course['id']) as $chapter) {
            if ($chapter['number'] < $deletedChapter['number']) {
                $prevChapter = $chapter;
            }
        }

        $tasks = $this->getTaskService()->findTasksByChapterId($deletedChapter['id']);

        foreach ($tasks as $task) {
            $this->getTaskService()->updateTask($task['id'], array('courseChapterId' => $prevChapter['id']));
        }
    }

    public function getChapter($courseId, $chapterId) 
    {
        $chapter = $this->getChapterDao()->get($chapterId);
        $course = $this->getCourseDao()->get($courseId);
        if($course['id'] == $chapter['courseId']) {
            return $chapter;
        }
        return array();
    }

    protected function hasCourseManagerRole($courseId, $userId)
    {
        return true;
    }

    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    protected function getChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }
}