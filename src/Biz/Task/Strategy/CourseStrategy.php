<?php

namespace Biz\Task\Strategy;


interface CourseStrategy
{
    //任务的api
    public function createTask($field);

    public function updateTask($id, $fields);

    public function deleteTask($task);

    public function canLearnTask($task);

    public function publishTask($task);

    public function unpublishTask($task);

    /**
     * 任务列表管理页面
     * @return page path
     */
    public function getTasksRenderPage();

    /**
     * @return 新增任务的列表片段页面
     */
    public function getTaskItemRenderPage();

    //课时的api
    public function prepareCourseItems($course, $tasks);

    public function sortCourseItems($courseId, array $itemIds);


}