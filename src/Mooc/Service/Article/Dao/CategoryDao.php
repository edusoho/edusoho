<?php

namespace Mooc\Service\Article\Dao;

interface CategoryDao
{
    public function findCategoriesByBranchSchoolId($branchSchoolId);
}
