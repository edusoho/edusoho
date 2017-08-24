<?php

namespace Biz\CloudPlatform\Service;

interface PushService
{
    public function push($from, $to, $body);

    public function pushUserFollow($user, $friend);

    public function pushUserUnFollow($user, $friend);

    public function pushCourseJoin($member);

    public function pushCourseQuit($member);

    public function pushClassroomJoin($member);

    public function pushClassroomQuit($member);

    public function pushArticleCreate($article);

    public function pushAnnouncementCreate($announcement);

    public function pushThreadCreate($thread);

    public function pushThreadPostCreate($threadPost);

    public function pushCouponReceived($coupon);
}
