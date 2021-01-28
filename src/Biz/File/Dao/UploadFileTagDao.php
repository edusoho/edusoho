<?php

namespace Biz\File\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UploadFileTagDao extends GeneralDaoInterface
{
    public function deleteByFileId($fileId);

    public function deleteByTagId($tagId);

    public function findByFileId($fileId);

    public function findByTagId($tagId);
}
