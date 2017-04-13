<?php

namespace Biz\Activity\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TestpaperActivityDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function findByMediaIds($mediaIds);
}
