<?php

namespace Topxia\Service\MyGroup\Dao;

interface MyGroupDao
{
	public function addGroup($group);
        public function searchGroup($condtion,$start,$limit,$sort);
        public function getGroupinfo($id);
        public function updatememberNum($id,$type);
        public function isowner($id,$userid);
        public function getownerId($id);
        public function updatgroupinfo($id,$condtion);
        public function updategroupinfo($id,$condtion);
}