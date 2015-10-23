<?php

namespace Topxia\Service\System\Dao;

interface SettingDao
{

    public function findAllSettings();

    public function addSetting($setting);

    public function deleteSettingByName($name);

}