<?php

namespace Biz\Course\Copy\Impl;

use Biz\Task\Dao\TaskDao;
use Biz\Activity\Config\Activity;
use Biz\Activity\Dao\ActivityDao;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseMaterialDao;
use Biz\Course\Copy\AbstractEntityCopy;

class TaskCopy extends AbstractEntityCopy
{
    /**
     * 复制链说明：
     * Task 任务信息
     * - Chapter 多级章节信息
     * - Activity 活动信息
     *   - ActivityConfig 活动自定义信息
     *   - Material 关联到activity的Material
     *
     * @param $biz
     */
    public function __construct($biz)
    {
        $this->biz      = $biz;
        $this->children = array();
    }

    /*
     * 这里同时处理task和chapter
     * $source = $originalCourse
     * $config = $newCourse
     */
    protected function _copy($source, $config = array())
    {
        $this->addError('TaskCopy', 'copy source:'.json_encode($source));
        $user  = $this->biz['user'];
        $tasks = $this->getTaskDao()->findByCourseId($source['id']);
        if (empty($tasks)) {
            return array();
        }

        $newCourse      = $config['newCourse'];
        $newCourseSetId = $newCourse['courseSetId'];
        $newTasks       = array();
        $chapterMap     = $this->doCopyChapters($source['id'], $newCourse['id']);
        $activityMap    = $this->doCopyActivities($source['id'], $newCourse['id'], $newCourseSetId);

        foreach ($tasks as $task) {
            $newTask                = $this->doCopyTask($task);
            $newTask['courseSetId'] = $newCourseSetId;
            $newTask['courseId']    = $newCourse['id'];
            if (!empty($task['categoryId'])) {
                $newTask['categoryId'] = $chapterMap[$task['categoryId']];
            }
            $newTask['activityId']    = $activityMap[$task['activityId']];
            $newTask['createdUserId'] = $user['id'];
            $newTasks[]               = $this->getTaskDao()->create($newTask);
        }

        return $newTasks;
    }

    private function doCopyChapters($courseId, $newCourseId)
    {
        //查询出course下所有chapter，新增并保留新旧chapter id，用于填充newTask的categoryId
        $chapters   = $this->getChapterDao()->findChaptersByCourseId($courseId);
        $chapterMap = array(); // key=oldChapterId,value=newChapterId
        if (!empty($chapters)) {
            //order by parentId
            usort($chapters, function ($a, $b) {
                //@todo 这个逻辑待测试
                return $a['parentId'] < $b['parentId'];
            });
            foreach ($chapters as $chapter) {
                $newChapter = array(
                    'courseId' => $newCourseId,
                    'type'     => $chapter['type'],
                    'number'   => $chapter['number'],
                    'seq'      => $chapter['seq'],
                    'title'    => $chapter['title'],
                    'copyId'   => $chapter['id']
                );
                if ($chapter['parentId'] > 0) {
                    $newChapter['parentId'] = $chapterMap[$chapter['parentId']];
                }
                $newChapter                 = $this->getChapterDao()->create($newChapter);
                $chapterMap[$chapter['id']] = $newChapter['id'];
            }
        }
        return $chapterMap;
    }

    private function doCopyActivities($courseId, $newCourseId, $courseSetId)
    {
        // 查询出course下所有activity，新增并保留新旧activity id，用于填充newTask的activityId
        $activities  = $this->getActivityDao()->findByCourseId($courseId);
        $activityMap = array();
        if (!empty($activities)) {
            foreach ($activities as $activity) {
                $newActivity = array(
                    'title'           => $activity['title'],
                    'remark'          => $activity['remark'],
                    'content'         => $activity['content'],
                    'length'          => $activity['length'],
                    'fromUserId'      => $this->biz['user']['id'],
                    'startTime'       => $activity['startTime'],
                    'endTime'         => $activity['endTime'],
                    'fromCourseId'    => $newCourseId,
                    'fromCourseSetId' => $courseSetId
                );
                $testId = 0;
                if (in_array($activity['mediaType'], array('homework', 'testpaper', 'exercise'))) {
                    $activityTestpaperCopy = new ActivityTestpaperCopy($this->biz);
                    $testpaper             = $activityTestpaperCopy->copy($newActivity);
                    $testId                = $testpaper['id'];
                }
                $config = $this->getActivityConfig($activity['mediaType']);
                $ext    = $config->copy($activity, array(
                    'refLiveroom' => $activity['fromCourseSetId'] != $courseSetId,
                    'testId'      => $testId
                ));
                if (!empty($ext)) {
                    $newActivity['mediaId'] = $ext['id'];
                }
                $newActivity = $this->getActivityDao()->create($newActivity);
                $this->doCopyMaterial($activity, $newActivity, array('newCourseId' => $newCourseId, 'newCourseSetId' => $courseSetId));
                $activityMap[$activity['id']] = $newActivity['id'];
            }
        }

        return $activityMap;
    }

    private function doCopyMaterial($activity, $newActivity, $config = array())
    {
        $materials = $this->getMaterialDao()->findMaterialsByLessonIdAndSource($activity['id'], 'courseactivity');
        if (empty($materials)) {
            return;
        }

        foreach ($materials as $material) {
            $newMaterial = array(
                'courseSetId' => $config['newCourseSetId'],
                'courseId'    => $config['newCourseId'],
                'lessonId'    => $newActivity['id'],
                'title'       => $material['title'],
                'description' => $material['description'],
                'link'        => $material['link'],
                'fileId'      => $material['fileId'],
                'fileUri'     => $material['fileUri'],
                'fileMime'    => $material['fileMime'],
                'fileSize'    => $material['fileSize'],
                'source'      => 'courseactivity',
                'userId'      => $this->biz['user']['id'],
                'type'        => $material['type'],
                'copyId'      => $material['id']
            );
            $this->getMaterialDao()->create($newMaterial);
        }
    }

    private function doCopyTask($task)
    {
        $fields = array(
            'seq',
            'activityId',
            'title',
            'isFree',
            'isOptional',
            'startTime',
            'endTime',
            'mode',
            'number',
            'type',
            'mediaSource'
        );

        $new = array();
        foreach ($fields as $field) {
            $new[$field] = $task[$field];
        }

        return $new;
    }

    /**
     * @param  $type
     * @return Activity
     */
    private function getActivityConfig($type)
    {
        return $this->biz["activity_type.{$type}"];
    }

    /**
     * @return TaskDao
     */
    protected function getTaskDao()
    {
        return $this->biz->dao('Task:TaskDao');
    }

    /**
     * @return ActivityDao
     */
    protected function getActivityDao()
    {
        return $this->biz->dao('Activity:ActivityDao');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getChapterDao()
    {
        return $this->biz->dao('Course:CourseChapterDao');
    }

    /**
     * @return CourseMaterialDao
     */
    protected function getMaterialDao()
    {
        return $this->biz->dao('Course:CourseMaterialDao');
    }
}
