<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\LearningDataAnalysisDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class FavoriteDaoImpl extends GeneralDaoImpl implements LearningDataAnalysisDao
{
    public function declares()
    {
        return array(
        );
    }

    public function findLearningCourses($condtions)
    {

    }
}
