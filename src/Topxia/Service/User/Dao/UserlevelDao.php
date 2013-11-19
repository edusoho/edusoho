<?php

namespace Topxia\Service\User\Dao;

interface UserlevelDao
{
    public function searchUserlevels($conditions, $start, $limit);

    public function searchUserlevelsCount($conditions);

    public function createUserlevel($userlevel);

    public function getUserlevel($id);

    public function updateUserlevel($id,$fields);

    public function deleteUserlevel($id);

    public function getUserlevelByName($name);

}