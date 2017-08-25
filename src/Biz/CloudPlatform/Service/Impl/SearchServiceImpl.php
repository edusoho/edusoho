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
        $api = CloudAPIFactory::create('leaf');

        $args = array(
            'type' => 'delete',
            'accessKey' => $api->getAccessKey(),
            'category' => $params['category'],
            'id' => $params['id'],
        );

        $result = $api->post('/search/notifications', $args);
        file_put_contents('3.txt', json_encode($args));
        file_put_contents('4.txt', json_encode($result));
    }

    public function notifyUpdate($params)
    {
        $api = CloudAPIFactory::create('leaf');

        $args = array(
            'type' => 'update',
            'accessKey' => $api->getAccessKey(),
            'category' => $params['category'],
        );

        $result = $api->post('/search/notifications', $args);
        file_put_contents('5.txt', json_encode($args));
        file_put_contents('6.txt', json_encode($result));
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
