<?php

namespace MaterialLib\Service\MaterialLib\Impl;

use Topxia\Common\ArrayToolkit;
use MaterialLib\Service\BaseService;
use MaterialLib\Service\MaterialLib\Permission;
use Topxia\Service\Common\AccessDeniedException;
use MaterialLib\Service\MaterialLib\MaterialLibService;

class MaterialLibServiceImpl extends BaseService implements MaterialLibService
{
    public function search($conditions, $start, $limit)
    {
        $this->checkPermission(Permission::SEARCH);
        $conditions['start']    = $start;
        $conditions['limit']    = $limit;
        $conditions             = $this->filterConditions($conditions);
        $result                 = $this->getCloudFileService()->search($conditions);
        $createdUserIds         = ArrayToolkit::column($result['data'], 'createdUserId');
        $result['createdUsers'] = $this->getUserService()->findUsersByIds($createdUserIds);

        return $result;
    }

    public function get($globalId)
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->get($globalId);
    }

    public function player($globalId)
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->player($globalId);
    }

    public function edit($globalId, $fields)
    {
        $this->checkPermission(Permission::EDIT, array('globalId' => $globalId));
        $this->getCloudFileService()->edit($globalId, $fields);
        $this->getUploadFileService()->edit($globalId, $fields);
    }

    public function delete($globalId)
    {
        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);

        if ($globalId) {
            $this->checkPermission(Permission::DELETE, array('file' => $file));
            $result = $this->getCloudFileService()->delete($globalId);

            if (isset($result['success']) && $result['success']) {
                $result = $this->getUploadFileService()->deleteByGlobalId($globalId);

                if ($result) {
                    $this->getUploadFileTagService()->deleteByFileId($file['id']);
                }

                return $result;
            }
        }

        return false;
    }

    public function batchDelete($ids)
    {
        $files     = $this->getUploadFileService()->findFilesByIds($ids);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        foreach ($globalIds as $key => $globalId) {
            $result = $this->delete($globalId);
        }

        return array('success' => true);
    }

    public function batchShare($ids)
    {
        $files     = $this->getUploadFileService()->findFilesByIds($ids);
        $globalIds = ArrayToolkit::column($files, 'globalId');

        foreach ($globalIds as $key => $value) {
            $this->checkPermission(Permission::EDIT, array('globalId' => $value));
            $fields = array('isPublic' => '1');

            $result = $this->getUploadFileService()->edit($value, $fields);

            if (!$result) {
                return false;
            } else {
                return true;
            }
        }

        return array('success' => true);
    }

    public function download($globalId)
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->download($globalId);
    }

    public function reconvert($globalId, $options = array())
    {
        $this->checkPermission(Permission::EDIT, array('globalId' => $globalId));
        return $this->getCloudFileService()->reconvert($globalId, $options);
    }

    public function getDefaultHumbnails($globalId)
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
        return $this->getCloudFileService()->getDefaultHumbnails($globalId);
    }

    public function getThumbnail($globalId, $options = array())
    {
        $this->checkPermission(Permission::VIEW, array('globalId' => $globalId));
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

    protected function filterConditions($conditions)
    {
        $noArray = array();

        if (!empty($conditions['tags'])) {
            $noArray[] = $this->findGlobalIdsByTags($conditions['tags']);
        }

        if (!empty($conditions['keywords']) && in_array($conditions['searchType'], array('title', 'user'))) {
            $noArray[] = $this->findGlobalIdsByKeyWords($conditions['searchType'], $conditions['keywords']);
        }

        $globalIds = array();

        for ($i = 0; $i < count($noArray); $i++) {
            if (empty($noArray[$i])) {
                $globalIds = array();
                break;
            }

            if ($i == 0) {
                $globalIds = $noArray[$i];
            } else {
                $globalIds = array_intersect($globalIds, $noArray[$i]);
            }
        }

        var_dump($globalIds);
        $conditions['nos'] = implode(',', $globalIds);

        $conditions = array_filter($conditions, function ($value) {
            if ($value === 0) {
                return true;
            }

            return !empty($value);
        });

        return $conditions;
    }

    protected function findGlobalIdsByTags($tags)
    {
        $filesInTags = $this->getUploadFileTagService()->findByTagId($tags);
        $fileIds     = ArrayToolkit::column($filesInTags, 'fileId');
        $files       = $this->getUploadFileService()->findLocalFilesByIds($fileIds);

        if (!empty($files)) {
            return ArrayToolkit::column($files, 'globalId');
        }

        return array();
    }

    protected function findGlobalIdsByKeyWords($searchType, $keywords)
    {
        if ($searchType == 'course') {
            $courses   = $this->getCourseService()->findCoursesByLikeTitle($conditions['keywords']);
            $courseIds = ArrayToolkit::column($courses, 'id');

            if (empty($courseIds)) {
                $courseIds = array('0');
            }

            $localFiles = $this->getUploadFileService()->findFilesByCourseIds($courseIds);
            $globalIds  = ArrayToolkit::column($localFiles, 'globalId');

            return $globalIds;
        } elseif ($conditions['searchType'] == 'user') {
            $users      = $this->getUserService()->searchUsers(array('nickname' => $conditions['keywords']), array('id', 'desc'), 0, 999);
            $userIds    = ArrayToolkit::column($users, 'id');
            $localFiles = $this->getMaterialLibDao()->findFilesByUserIds($userIds, $conditions['start'], $conditions['limit']);
            $globalIds  = ArrayToolkit::column($localFiles, 'globalId');
            return $globalIds;
        }

        return array();
    }

    protected function checkPermission($permission, $options = array())
    {
        if (!$this->getPermissionService()->check($permission, $options)) {
            throw new AccessDeniedException("无权限操作", 403);
        }
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }

    protected function getMaterialLibDao()
    {
        return $this->createDao('MaterialLib:MaterialLib.MaterialLibDao');
    }

    protected function getPermissionService()
    {
        return $this->createService('MaterialLib:MaterialLib.PermissionService');
    }

    protected function getCloudFileService()
    {
        return $this->createService('MaterialLib:MaterialLib.CloudFileService');
    }

    protected function getUploadFileTagService()
    {
        return $this->createService('File.UploadFileTagService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->createService('User.UserService');
    }
}
