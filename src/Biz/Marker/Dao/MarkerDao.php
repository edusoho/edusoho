<?php

namespace Biz\Marker\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MarkerDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function findByMediaId($mediaId);
}
