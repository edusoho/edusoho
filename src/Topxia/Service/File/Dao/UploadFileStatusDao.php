<?php

namespace Topxia\Service\File\Dao;

interface UploadFileStatusDao
{
	public function addUploadFileStatus(array $fields);

	public function updateUploadFileStatus($key, $fields);

	public function getUploadFileStatus($id);

	public function getUploadFileStatusByKey($key);

	public function deleteUploadFileStatus($key);
}