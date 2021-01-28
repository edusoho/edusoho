<?php

namespace Biz\FaceInspection\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserFaceDao extends GeneralDaoInterface
{
    public function getByUserId($userId);
}
