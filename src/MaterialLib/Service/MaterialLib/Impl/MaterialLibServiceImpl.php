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
        if(empty($conditions['tags'])) {
          unset($conditions['tags']);
          $filterConditions = $this->filterKeyWords($conditions);
          if(isset($filterConditions['nos'])) {
            if(empty($filterConditions['nos'])) {
              $filterConditions['nos'] = 0;
            } else {
              $filterConditions['nos'] = implode(',',$filterConditions['nos']);
            }
          }
        } elseif (empty($conditions['keywords'])) {
          unset($conditions['searchType']);
          $filterConditions = $this->filterTags($conditions);
          if(isset($filterConditions['nos'])) {
            if(empty($filterConditions['nos'])) {
              $filterConditions['nos'] = 0;
            } else {
              $filterConditions['nos'] = implode(',',$filterConditions['nos']);
            }
          }
        } else {
          $filterKeyWordsConditions = $this->filterKeyWords($conditions);
          $filterTagsConditions = $this->filterTags($conditions);
          $filterConditions = $this->mergeConditions($filterKeyWordsConditions,$filterTagsConditions);
          var_dump($filterConditions['nos']);
        }
        $filterConditions = array_filter($filterConditions, function ($value) {
            if ($value === 0) {
                return true;
            }

            return !empty($value);
        });
        return $filterConditions;
    }

    protected function filterTags($conditions)
    {
      if(!empty($conditions['tags'])) {
        $filesInTags = $this->getUploadFileTagService()->findByTagId($conditions['tags']);
        $fileIds     = ArrayToolkit::column($filesInTags, 'fileId');
        $files = $this->getUploadFileService()->findFilesByIds($fileIds);

        if($files) {
          $conditions['nos'] = ArrayToolkit::column($files, 'globalId');
        } else {
          $conditions['nos'] = array(0) ;
        }
      }
      unset($conditions['tags']);
      return $conditions;
    }

    protected function mergeConditions($filterKeyWordsConditions,$filterTagsConditions)
    {
      if(!empty($filterKeyWordsConditions['nos']) && !empty($filterTagsConditions['nos'])) {
        var_dump($filterKeyWordsConditions['nos']);
        var_dump($filterTagsConditions['nos']);
        $filterTagsConditions['nos'] = array_intersect($filterKeyWordsConditions['nos'],$filterTagsConditions['nos']);
        //var_dump($filterTagsConditions['nos']);
        if(empty($filterTagsConditions['nos'])) {
          $filterTagsConditions['nos'] = 0 ;
        } else {
          $filterTagsConditions['nos'] = implode(',',$filterTagsConditions['nos']);
        }
        return $filterTagsConditions;
      } else {
        $conditions = array_merge($filterKeyWordsConditions,$filterTagsConditions);
        if(isset($conditions['nos'])) {
          $conditions['nos'] = implode(',',$conditions['nos']);
        }
        //unset($conditions['nos']);
        return $conditions;
      }
    }

    protected function filterKeyWords($conditions)
    {
      if (!empty($conditions['keywords'])) {
          if ($conditions['searchType'] == 'title') {
              $localFiles              = $this->getUploadFileService()->searchFiles(array('filename' => $conditions['keywords']), array('createdTime', 'desc'), $conditions['start'], $conditions['limit']);
              $globalIds               = ArrayToolkit::column($localFiles, 'globalId');
              $conditions['nos'] = $globalIds;
          } elseif ($conditions['searchType'] == 'course') {
              $courses = $this->getCourseService()->findCoursesByLikeTitle($conditions['keywords']);
              $courseIds = ArrayToolkit::column($courses, 'id');
              if (empty($courseIds)) {
                  $courseIds = array('0');
              }
              $localFiles = $this->getUploadFileService()->findFilesByCourseIds($courseIds);
              $globalIds               = ArrayToolkit::column($localFiles, 'globalId');

              $conditions['nos'] = $globalIds;
              unset($conditions['keywords']);
              unset($conditions['courseIds']);
          } elseif ($conditions['searchType'] == 'user') {
              $users                        = $this->getUserService()->searchUsers(array('nickname' => $conditions['keywords']), array('id', 'desc'), 0, 999);
              $userIds                      = ArrayToolkit::column($users, 'id');
              $localFiles              = $this->getMaterialLibDao()->findFilesByUserIds($userIds, $conditions['start'], $conditions['limit']);
              $globalIds               = ArrayToolkit::column($localFiles, 'globalId');
              $conditions['nos'] = $globalIds;
              unset($conditions['createdUserIds']);
              unset($conditions['keywords']);
          }
      }
      unset($conditions['searchType']);
      // unset($conditions['keywords']);
      return $conditions;
    }

    public function filterTagCondition($conditions, $files)
    {
        if (!empty($conditions['tags'])) {
            $filesInTags = $this->getUploadFileTagService()->findByTagId($conditions['tags']);
            $fileIds     = ArrayToolkit::column($filesInTags, 'fileId');

            if (isset($files['data'])) {
                $filterFiles = array();

                foreach ($files['data'] as $key => $file) {
                    if (in_array($file['extno'], $fileIds)) {
                        array_push($filterFiles, $file);
                    }
                }

                $files['data']  = $filterFiles;
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
