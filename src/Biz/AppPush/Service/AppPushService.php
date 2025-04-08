<?php

namespace Biz\AppPush\Service;

interface AppPushService
{
    public function bindDevice($params);

    public function unbindDevice($userId);
}
