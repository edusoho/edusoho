<?php

namespace Topxia\Service\File\Dao;

interface UploadFileCollectDao
{
    public function getCollectonByUserIdandFileId($userId, $fileId);

    public function addCollection($collection);

    public function deleteCollection($id);
}
