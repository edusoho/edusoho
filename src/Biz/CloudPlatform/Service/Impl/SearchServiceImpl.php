<?php

namespace Biz\CloudPlatform\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\CloudPlatform\Service\SearchService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;

class SearchServiceImpl extends BaseService implements SearchService
{
    public function notifyDelete($params)
    {
        $args = array(
            'type' => 'delete',
            'category' => $params['category'],
            'id' => $params['id'],
        );

        CloudAPIFactory::create('leaf')->post('/search/notifications', $args);
    }

    public function notifyUpdate($params)
    {
        $args = array(
            'type' => 'update',
            'category' => $params['category'],
            'id' => $params['id'],
        );

        CloudAPIFactory::create('leaf')->post('/search/notifications', $args);

    }

    public function notifyUserCreate($user)
    {
        $this->notifyUpdate($user);
    }

    public function notifyUserUpdate($user)
    {
        $this->notifyUpdate($user);
    }

    public function notifyUserDelete($user)
    {
        $this->notifyDelete($user);
    }

    public function notifyCourseCreate($course)
    {
        $this->notifyUpdate($course);
    }

    public function notifyCourseUpdate($course)
    {
        $this->notifyUpdate($course);
    }

    public function notifyCourseDelete($course)
    {
        $this->notifyDelete($course);
    }

    /**
     * @param create = publish
     */
    public function notifyTaskCreate($task)
    {
        $this->notifyUpdate($task);
    }

    public function notifyTaskUpdate($task)
    {
        $this->notifyUpdate($task);
    }

    public function notifyTaskDelete($task)
    {
        $this->notifyDelete($task);
    }

    public function notifyArticleCreate($article)
    {
        $this->notifyUpdate($article);
    }

    public function notifyArticleUpdate($article)
    {
        $this->notifyUpdate($article);
    }

    public function notifyArticleDelete($article)
    {
        $this->notifyDelete($article);
    }

    public function notifyThreadCreate($thread)
    {
        $this->notifyUpdate($thread);
    }

    public function notifyThreadUpdate($thread)
    {
        $this->notifyUpdate($thread);
    }

    public function notifyThreadDelete($thread)
    {
        $this->notifyDelete($thread);
    }

    public function notifyOpenCourseCreate($openCourse)
    {
        $this->notifyUpdate($openCourse);
    }

    public function notifyOpenCourseUpdate($openCourse)
    {
        $this->notifyUpdate($openCourse);
    }

    public function notifyOpenCourseDelete($openCourse)
    {
        $this->notifyDelete($openCourse);
    }

    public function notifyOpenCourseLessonCreate()
    {
        // TODO:暂无
    }

    public function notifyOpenCourseLessonUpdate()
    {
        // TODO:暂无
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
