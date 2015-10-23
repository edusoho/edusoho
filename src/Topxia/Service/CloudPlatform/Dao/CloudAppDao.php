<?php

namespace Topxia\Service\CloudPlatform\Dao;

interface CloudAppDao 
{
    public function getApp($id);

    public function getAppByCode($code);

    public function findAppsByCodes(array $codes);

    public function findApps($start, $limit);

    public function findAppCount();

    public function addApp($app);

    public function updateApp($id,$app);

    public function deleteApp($id);
}