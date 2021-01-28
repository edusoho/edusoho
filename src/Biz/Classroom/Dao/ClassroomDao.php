<?php

namespace Biz\Classroom\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ClassroomDao extends GeneralDaoInterface
{
    public function findByIds($ids);

    public function getByTitle($title);

    public function findProductIdAndGoodsIdsByIds($ids);

    public function findByLikeTitle($title);

    public function refreshHotSeq();
}
