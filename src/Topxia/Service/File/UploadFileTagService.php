<?php
namespace Topxia\Service\File;

interface UploadFileTagService
{
	public function get($id);

	public function delete($id);
	
	public function edit($fileIds, $tagIds);

	public function findByFileId($fileId);
}