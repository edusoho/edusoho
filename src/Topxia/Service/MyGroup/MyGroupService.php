<?php

namespace Topxia\Service\MyGroup;

interface MyGroupService
{
    public function addGroup($group);
    public function searchGroup($condtion,$start,$limit,$sort);
    public function getGroupinfo($id);
    public function isowner($id,$userid) ;
    public function getgroupowner_info($id);
    public function changegrouplogo($id, $pictureFilePath, $options);
    public function changegroupbackgroundlogo($id, $pictureFilePath, $options);
    public function updategroupinfo($id,$condtion);
    public function getAllgroupinfo($condtion,$sort,$start,$limit);
    public function getAllgroupCount($condtion);
    public function openGroup($id);
    public function closeGroup($id);
}