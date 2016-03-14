<?php

namespace Topxia\Service\File\Dao;

interface UploadFileTagDao
{
    public function get($id);

    public function add($fields);

    public function delete($id);

    public function deleteByFileId($fileId);

    public function findByFileId($fileId);

    public function findByTagId($tagId, $start, $limit);
}
