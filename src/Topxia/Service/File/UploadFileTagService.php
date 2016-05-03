<?php
namespace Topxia\Service\File;

interface UploadFileTagService
{
	public function get($id);

	public function delete($id);

	public function edit($fileIds, $tagIds);

	public function deleteByFileId($fileId);

	public function deleteByTagId($tagId);

	public function findByFileId($fileId);

	public function findByTagId($tagId);
}
