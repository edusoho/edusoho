<?php

namespace Topxia\Service\User\Dao;

interface DiskFileDao
{
	public function getFile($id);

    public function addFile(array $file);
}