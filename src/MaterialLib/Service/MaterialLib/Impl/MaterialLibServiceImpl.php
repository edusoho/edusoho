<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use Topxia\Common\ArrayToolkit;
use MaterialLib\Service\BaseService;
use Topxia\Service\Common\AccessDeniedException;
use MaterialLib\Service\MaterialLib\MaterialLibService;

class MaterialLibServiceImpl extends BaseService implements MaterialLibService
{
    public function get($id)
    {
        //$this->checkPermission(Permission::VIEW, array('id' => $id));
        return $this->getUploadFileService()->getFile2($id);
    }

    public function getByGlobalId($globalId)
    {
        return $this->getUploadFileService()->getFileByGlobalId2($globalId);
    }

    public function player($globalId)
    {
        //$this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->player($globalId);
    }

    public function edit($fileId, $fields)
    {
        //$this->checkPermission(Permission::EDIT, array('globalId' => $globalId));
        $this->getUploadFileService()->edit($fileId, $fields);
    }

    public function delete($id)
    {
        //$this->checkPermission(Permission::DELETE, array('file' => $file));
        $result = $this->getUploadFileService()->deleteFile2($id);

        if ($result) {
            return true;
        }

        return false;
    }

    public function batchDelete($ids)
    {
        foreach ($ids as $key => $id) {
            $result = $this->delete($id);
        }

        return array('success' => true);
    }

    public function batchTagEdit($fileIds, $tagNames)
    {
        $tagNames = explode(',', $tagNames);

        foreach ($fileIds as $key => $fileId) {
            foreach ($tagNames as $key => $tagName) {
                $tag = $this->getTagService()->getTagByName($tagName);

                $result     = $this->getUploadFileTagService()->findByFileId($fileId);
                $fileTagIds = ArrayToolkit::column($result, 'tagId');

                if (!in_array($tag['id'], $fileTagIds)) {
                    $this->getUploadFileTagService()->add(array(
                        'fileId' => $fileId,
                        'tagId'  => $tag['id']
                    ));
                    $result = $this->getUploadFileTagService()->findByFileId($fileId);

                    $tagIds       = ArrayToolkit::column($result, 'tagId');
                    $tags         = $this->getTagService()->findTagsByIds($tagIds);
                    $editTagNames = ArrayToolkit::column($tags, 'name');

                    $conditions         = array();
                    $conditions['tags'] = implode(',', $editTagNames);

                    $this->getUploadFileService()->edit($fileId, $conditions);
                }
            }
        }
    }

    public function batchShare($ids)
    {
        foreach ($ids as $key => $id) {
            //$this->checkPermission(Permission::EDIT, array('globalId' => $value));
            $fields = array('isPublic' => '1');

            $this->getUploadFileService()->edit($id, $fields);
        }

        return array('success' => true);
    }

    public function download($id)
    {
        //$this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getUploadFileService()->getDownloadFile($id);
    }

    public function reconvert($globalId, $options = array())
    {
        //$this->checkPermission(Permission::EDIT, array('globalId' => $globalId));
        $result = $this->getCloudFileService()->reconvert($globalId, $options);
        $file   = $this->getByGlobalId($globalId);
        return $file;
    }

    public function getDefaultHumbnails($globalId)
    {
        //$this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->getDefaultHumbnails($globalId);
    }

    public function getThumbnail($globalId, $options = array())
    {
        //$this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->getThumbnail($globalId, $options);
    }

    public function getStatistics($options = array())
    {
        return $this->getCloudFileService()->getStatistics($options);
    }

    public function synData()
    {
        $conditions = array(
            'globalId' => '0'
        );
        $oldFiles = $this->getCloudFileService()->synData($conditions);
        return $oldFiles;
    }

    // protected function checkPermission($permission, $options = array())
    // {
    //     if (!$this->getPermissionService()->checkPermission($permission, $options)) {
    //         throw new AccessDeniedException("无权限操作", 403);
    //     }
    // }

    protected function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    // protected function getPermissionService()
    // {
    //     return $this->createService('MaterialLib:MaterialLib.PermissionService');
    // }

    protected function getCloudFileService()
    {
        return $this->createService('CloudFile.CloudFileService');
    }

    protected function getUploadFileTagService()
    {
        return $this->createService('File.UploadFileTagService');
    }
}
