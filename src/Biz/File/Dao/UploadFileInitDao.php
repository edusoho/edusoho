<?php

namespace Biz\File\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UploadFileInitDao extends GeneralDaoInterface
{
    public function getByGlobalId($globalId);
}
