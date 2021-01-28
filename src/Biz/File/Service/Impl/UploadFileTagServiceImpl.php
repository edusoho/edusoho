<?php

namespace Biz\File\Service\Impl;

use Biz\BaseService;
use Biz\File\Dao\UploadFileTagDao;
use Biz\File\Service\UploadFileTagService;

class UploadFileTagServiceImpl extends BaseService implements UploadFileTagService
{
    public function get($id)
    {
        return $this->getUploadFileTagDao()->get($id);
    }

    public function add($fields)
    {
        return $this->getUploadFileTagDao()->create($fields);
    }

    public function findByFileId($fileId)
    {
        return $this->getUploadFileTagDao()->findByFileId($fileId);
    }

    public function findByTagId($tagId)
    {
        return $this->getUploadFileTagDao()->findByTagId($tagId);
    }

    public function delete($id)
    {
        return $this->getUploadFileTagDao()->delete($id);
    }

    public function deleteByFileId($fileId)
    {
        $result = $this->getUploadFileTagDao()->deleteByFileId($fileId);

        return $result;
    }

    public function deleteByTagId($tagId)
    {
        $result = $this->getUploadFileTagDao()->deleteByTagId($tagId);

        return $result;
    }

    public function edit($fileIds, $tagIds)
    {
        foreach ($fileIds as $fileId) {
            $tags = $this->getUploadFileTagDao()->findByFileId($fileId);
            if ($tags) {
                $this->getUploadFileTagDao()->deleteByFileId($fileId);
            }
            foreach ($tagIds as $tagId) {
                $condition = array(
                    'fileId' => $fileId,
                    'tagId' => $tagId,
                );
                $result = $this->getUploadFileTagDao()->create($condition);
            }
        }
        if (empty($result)) {
            $result = array();
        }

        return $result;
    }

    /**
     * @return UploadFileTagDao
     */
    protected function getUploadFileTagDao()
    {
        return $this->createDao('File:UploadFileTagDao');
    }
}
