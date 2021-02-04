<?php

namespace OpenLivePlugin\Biz\User\Service;

use Biz\User\Service\UserService;

interface PluginUserService extends UserService
{
    public function searchSpeakers($searchStr);
}
