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
        $tags = $this->getUploadFileTagService()->findByFileId($file['id']);

        if ($globalId) {
            $this->checkPermission(Permission::DELETE, array('globalId' => $globalId));
            $result = $this->getCloudFileService()->delete($globalId);
            if (isset($result['success']) && $result['success']) {
                $result = $this->getUploadFileService()->deleteByGlobalId($globalId);

                if ($result) {
                  $this->getUploadFileTagService()->deleteByFileId($file['id']);
                    // foreach ($tags as $tag) {
                    //
                    //     $this->getUploadFileTagService()->delete($tag['id']);
                    // }

                }
                return $result;
            }
            return false;
        }
    }

    public function batchDelete($ids)
    {
        $files     = $this->getUploadFileService()->findFilesByIds($ids);
        $globalIds = ArrayToolkit::column($files, 'globalId');
        foreach ($globalIds as $key => $value) {
            $this->checkPermission(Permission::DELETE, array('globalId' => $value));
            $result = $this->getCloudFileService()->delete($value);
            if (isset($result['success']) && $result['success']) {
                $result = $this->getUploadFileService()->deleteByGlobalId($value);
                if (!$result) {
                    return false;
                }
            } else {
                return false;
            }
        }
        $fileIds = ArrayToolkit::column($files, 'id');
        $tagIds = array();
        $this->getUploadFileTagService()->edit($fileIds,$tagIds);
        return array('success' => true);
    }

    public function batchShare($ids)
    {
        $files = $this->getUploadFileService()->findFilesByIds($ids);
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
        if (!empty($conditions['keywords'])) {
            if ($conditions['searchType'] == 'title') {
                $conditions['name'] = $conditions['keywords'];

            } elseif ($conditions['searchType'] == 'course') {
                $courses = $this->getCourseService()->findCoursesByLikeTitle($conditions['keywords']);

                $courseIds = ArrayToolkit::column($courses, 'id');

                $conditions['courseIds'] = $courseIds;
            } elseif ($conditions['searchType'] == 'user') {
                $users = $this->getUserService()->searchUsers(array('nickname'=>$conditions['keywords']), array('id','desc'),0,999);
                $userIds = ArrayToolkit::column($users, 'id');
                $conditions['createdUserIds'] = $userIds;

            }
        }
        unset($conditions['searchType']);
        unset($conditions['keywords']);
        unset($conditions['tags']);
        $filterConditions = array_filter($conditions, function ($value) {
            if ($value === 0) {
                return true;
            }
            return !empty($value);
        });

        if (!empty($filterConditions['name'])) {
            $localFiles = $this->getUploadFileService()->searchFiles(array('filename'=>$filterConditions['name']), array('createdTime', 'desc'), $filterConditions['start'], $filterConditions['limit']);
            $globalIds = ArrayToolkit::column($localFiles, 'globalId');
            $filterConditions['nos'] = implode(',', $globalIds);
        }
        if (!empty($filterConditions['createdUserIds'])) {
            $localFiles = $this->getMaterialLibDao()->findFilesByUserIds($filterConditions['createdUserIds'], $filterConditions['start'], $filterConditions['limit']);
            $globalIds               = ArrayToolkit::column($localFiles, 'globalId');
            $filterConditions['nos'] = implode(',', $globalIds);
            unset($filterConditions['createdUserIds']);
        }

        if (!empty($filterConditions['courseIds'])) {
            $localFiles              = $this->getUploadFileService()->findFilesByCourseIds($filterConditions['courseIds']);
            $globalIds               = ArrayToolkit::column($localFiles, 'globalId');
            $filterConditions['nos'] = implode(',', $globalIds);
            unset($filterConditions['courseIds']);
        }

        return $filterConditions;
    }

    public function filterTagCondition($conditions,$files)
    {
      if(!empty($conditions['tags'])) {

        $filesInTags = $this->getUploadFileTagService()->findByTagId($conditions['tags']);
        $fileIds = ArrayToolkit::column($filesInTags,'fileId');
        if(isset($files['data'])) {
            $filterFiles = array();
            foreach ($files['data'] as $key => $file) {
              if(in_array($file['extno'],$fileIds)) {
                array_push($filterFiles,$file);
              }
            }

          $files['data'] = $filterFiles;
          $files['count'] = count($filterFiles);
          return $files;
        }
      }
      return $files;
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
