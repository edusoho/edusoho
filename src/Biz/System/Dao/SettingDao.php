<?php

namespace Biz\System\Dao;

interface SettingDao
{
    public function findAllSettings();

    public function addSetting($setting);

    public function deleteSettingByName($name);

}
