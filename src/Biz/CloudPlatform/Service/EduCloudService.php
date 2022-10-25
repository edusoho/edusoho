<?php

namespace Biz\CloudPlatform\Service;

interface EduCloudService
{
    public function isVisibleCloud();

    public function getOldSmsUserStatus();

    public function uploadCallbackUrl();

    public function getLevel();

    public function isSaaS();
}
