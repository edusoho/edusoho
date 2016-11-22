<?php

namespace Biz\Course\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseService;

class CourseServiceImpl extends BaseService implements CourseService
{
    public function getCourseItems($courseId)
    {
        $items = array();
        $user = $this->getCurrentUser();
        $tasks = $this->getTaskService()->findUserTasksByCourseId($courseId, $user['id']);
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