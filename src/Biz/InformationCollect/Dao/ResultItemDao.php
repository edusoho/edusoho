<?php

namespace Biz\InformationCollect\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ResultItemDao extends AdvancedDaoInterface
{
    public function findResultDataByResultIds($resultIds);

    public function findByResultId($resultId);
}
