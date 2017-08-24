<?php

namespace Biz\CloudPlatform\Service;

interface SearchService
{
    public function notifyUpdate($params);

    public function notifyDelete($params);

    public function notifyUserCreate($user);

    public function notifyUserUpdate($user);

    public function notifyUserDelete($user);

    public function notifyCourseCreate($course);

    public function notifyCourseUpdate($course);

    public function notifyCourseDelete($course);

    public function notifyArticleCreate($article);

    public function notifyArticleUpdate($article);

    public function notifyArticleDelete($article);

    public function notifyThreadCreate($thread);

    public function notifyThreadUpdate($thread);

    public function notifyThreadDelete($thread);

    public function notifyOpenCourseCreate($openCourse);

    public function notifyOpenCourseUpdate($openCourse);

    public function notifyOpenCourseDelete($openCourse);
}
