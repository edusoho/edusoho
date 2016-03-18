<?php

namespace Topxia\Service\File\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\UploadFileTagService;

class UploadFileTagServiceImpl extends BaseService implements UploadFileTagService
{
	public function get($id)
	{
		return $this->getUploadFileTagDao()->get($id);
	}

	public function delete($id)
	{
		return $thid->getUploadFileTagDao()->delete($id);
	}

	public function edit($fileIds, $tagIds)
	{
		foreach ($fileIds as $fileId ) {
			$tags = $this->getUploadFileTagDao()->findByFileId($fileId);
			foreach ($tagIds as $tagId ) {
				$condition = array(
					'fileId' => $fileId,
					'tagId' => $tagId
					);
				$result = $this->getUploadFileTagDao()->add($condition);
			
			}
			if (empty($result)) {
				$result = NULL;
			}
			if ($tags) {
				foreach ($tags as $tag ) {
					$this->getUploadFileTagDao()->delete($tag['id']);
				}
			} 
		}
		return $this->getUploadFileTagDao()->get($result);

	}

	protected function getUploadFileTagDao()
    {
        return $this->createDao('File.UploadFileTagDao');
    }
}