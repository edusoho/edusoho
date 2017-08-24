<?php

namespace Biz\CloudPlatform\Service;

interface PushService
{
    public function push($from, $to, $body);

    public function pushArticleCreate($article);

    public function pushAnnouncementCreate($announcement);

    public function pushThreadCreate($thread);

    public function pushThreadPostCreate($threadPost);
}
