<?php

namespace Biz\Task\Service;

interface TaskService
{
    const LEARN_TIME_STEP = 2;

    public function getTask($id);

    public function createTask($task);

    public function updateTask($id, $fields);

    public function updateSeq($id, $fields);

    public function updateTasks($Ids, $fields);

    public function publishTask($id);

    public function unpublishTask($id);

    public function deleteTask($id);

    public function findTasksByCourseId($courseId);

    public function findTasksByCourseIds($courseIds);

    public function countTasksByCourseId($courseId);

    public function findTasksByActivityIds($activityIds);

    public function search($conditions, $orderBy, $start, $limit);

    public function count($conditions);

    /**
     * @param  array   $ids
     * @return array
     */
    public function findTasksByIds(array $ids);

    public function findTasksFetchActivityByCourseId($courseId);

    public function findTasksFetchActivityAndResultByCourseId($courseId);

    /**
     * for question and testpaper ranges
     * @param  [type]  $userId
     * @param  [type]  $courseSetId
     * @return array
     */
    public function findUserTeachCoursesTasksByCourseSetId($userId, $courseSetId);

    public function startTask($taskId);

    public function doTask($taskId, $time = self::LEARN_TIME_STEP);

    public function finishTask($taskId);

    public function isFinished($taskId);

    public function tryTakeTask($taskId);

    public function trigger($id, $eventName, $data = array());

    /**
     * return next Task that can be learned of the  course plan, or return empty array()
     * @param  $taskId
     * @return mixed
     */
    public function getNextTask($taskId);

    /**
     *  return if the task can learn or not
     * @param  $taskId
     * @return True|False
     */
    public function canLearnTask($taskId); // 任务是否可学

    /**
     * return if the task has been learned
     * @param  $taskId
     * @return True|False
     */
    public function isTaskLearned($taskId);

    public function getMaxSeqByCourseId($courseId);

    public function findTasksByChapterId($chapterId);

    public function findTasksFetchActivityByChapterId($chapterId);

    public function finishTaskResult($taskId);

    public function findFreeTasksByCourseId($courseId);

    /**
     * 统计当前时间以后每天的直播次数
     *
     * @param $courseIds
     * @param $limit
     *
     * @return array<string, int|string>
     */
    public function findFutureLiveDatesByCourseIdsGroupByDate($courseIds, $limit);

    /**
     * 返回当前正在直播的直播任务
     *
     * @return array
     */
    public function findCurrentLiveTasks();

    /**
     * 返回当前将要直播的直播任务
     *
     * @return array
     */
    public function findFutureLiveTasks();

    /**
     *
     * 自由式
     * 1.获取所有的在学中的任务结果，如果为空，则学员学员未开始学习或者已经学完，取第一个任务作为下一个学习任务，
     * 2.如果不为空，则按照任务序列返回第一个作为下一个学习任务
     * 任务式
     * 1.获取所有的学完的任务结果，如果为空，则学员学员未开始学习或者已经学完，取第前三个作为任务，
     * 2.如果不为空，则取关联的三个。
     *
     * 自由式和任务式的逻辑由任务策略完成
     * @param  $courseId
     * @return array       tasks
     */
    public function findToLearnTasksByCourseId($courseId);

    public function getTaskByCourseIdAndActivityId($courseId, $activityId);

    /**
    * 获得课程的总学习时间
    */ 
    public function getLearnTimeByCourseSetId($courseSetId);
}
