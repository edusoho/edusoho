<?php

namespace Topxia\Service\File\Dao;

interface UploadFileInitDao
{
    public function getFile($id);

    public function getFileByGlobalId($globalId);

    public function addFile(array $file);

    public function updateFile($id, array $fields);

}
