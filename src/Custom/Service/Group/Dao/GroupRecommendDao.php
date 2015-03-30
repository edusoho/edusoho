<?php

namespace Custom\Service\Group\Dao;

interface GroupDao
{
    const TABLENAME = 'groups_recommend';

    public function getGroupRecommend($id);

    public function addGroupRecommend($group);

    public function updateGroupRecommend($id, $fields);

    public function deleteGroupRecommend($id);
    

}