<?php

namespace Topxia\Service\File\Dao;

interface UploadFileCollectDao
{
    public function findCollectonsByUserIdandFileIds($ids, $userId);

    public function getCollectonByUserIdandFileId($userId, $fileId);

    public function addCollection($collection);

    public function deleteCollection($id);

    public function findCollectionsByUserId($userId);
}
