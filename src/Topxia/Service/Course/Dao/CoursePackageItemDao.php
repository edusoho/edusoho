<?php

namespace Topxia\Service\Course\Dao;

interface CoursePackageItemDao
{
	public function getRelation($id);

    public function findRelationsByPackgeId($packageId);

    public function addRelation($relation);

    public function delete($id);
}