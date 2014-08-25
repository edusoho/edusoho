<?php

namespace Topxia\Service\CloudPlatform\Dao;

interface CloudAppDao 
{
    public function getApp($id);

    public function getAppByCode($code);

    public function findAppsByCodes(array $codes);

    public function findApps($start, $limit);

    public function findAppCount();

    public function addApp($App);

    public function updateApp($id,$App);

    public function deleteApp($id);
    
    public function updateAppVersion($code,$version);
    
    public function updateAppFromVersion($code,$fromVersion);
}