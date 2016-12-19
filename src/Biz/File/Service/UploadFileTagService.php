<?php
namespace Biz\File\Service;

interface UploadFileTagService
{
	public function get($id);

    public function add($fields);

	public function delete($id);

	public function edit($fileIds, $tagIds);

	public function deleteByFileId($fileId);

	public function deleteByTagId($tagId);

	public function findByFileId($fileId);

	public function findByTagId($tagId);
}
