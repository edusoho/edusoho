<?php

namespace Biz\Marker\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MarkerDao extends AdvancedDaoInterface
{
    public function getByMediaIdAndSecond($mediaId, $second);

    public function findByIds($ids);
}
