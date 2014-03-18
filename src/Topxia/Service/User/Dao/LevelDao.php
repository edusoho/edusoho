<?php

namespace Topxia\Service\User\Dao;

interface LevelDao
{
    public function getLevel($id);

    public function getLevelByName($name);

    public function searchLevels($conditions, $start, $limit);

    public function searchLevelsCount($conditions);

    public function createLevel($level);

    public function updateLevel($id,$fields);

    public function deleteLevel($id);

}