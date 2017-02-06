<?php

namespace Biz\Testpaper\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TestpaperDao extends GeneralDaoInterface
{
    public function findTestpapersByIds(array $ids);

    public function findTestpapersByCopyIdAndLockedTarget($copyId, $lockedTarget);

    public function getTestpaperByCopyIdAndCourseSetId($copyId, $courseSetId);

    public function deleteByCourseSetId($courseSetId);
}
