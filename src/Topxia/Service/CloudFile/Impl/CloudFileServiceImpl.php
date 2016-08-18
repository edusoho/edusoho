<?php

namespace Topxia\Service\CloudFile\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\CloudFile\CloudFileService;

class CloudFileServiceImpl extends BaseService implements CloudFileService
{
    public function search($conditions, $start, $limit)
    {
        if (empty($conditions['resType'])) {
            $conditions['start'] = $start;
            $conditions['limit'] = $limit;
            $conditions          = $this->filterConditions($conditions);
            $result              = $this->getCloudFileImplementor()->search($conditions);

            if (!empty($result['data'])) {
                $createdUserIds = array();

                foreach ($result['data'] as &$cloudFile) {
                    $file = $this->getUploadFileService()->getFileByGlobalId($cloudFile['no']);

                    if (!empty($file)) {
                        $createdUserIds[]           = $file['createdUserId'];
                        $cloudFile['createdUserId'] = $file['createdUserId'];
                    }
                }

                $result['createdUsers'] = ArrayToolkit::index($this->getUserService()->findUsersByIds($createdUserIds), 'id');
            }
        } else {
            $conditions['targetType'] = $conditions['resType'];
            $result['count']          = $this->getUploadFileService()->searchFileCount($conditions);
            $result['data']           = $this->getUploadFileService()->searchFiles($conditions, array('id', 'DESC'), $start, $limit);

            $createdUserIds         = ArrayToolkit::column($result['data'], 'createdUserId');
            $result['createdUsers'] = $this->getUserService()->findUsersByIds($createdUserIds);

            $result['data'] = array_map(function ($file) {
                $file['no']            = $file['globalId'];
                $file['processStatus'] = empty($file['processStatus']) ? 'none' : $file['processStatus'];
                return $file;
            }, $result['data']);
        }
        return $result;
    }

    protected function filterConditions($conditions)
    {
        $noArray = array();

        if (!empty($conditions['tags'])) {
            $noArray[] = $this->findGlobalIdsByTags($conditions['tags']);
        }

        if (!empty($conditions['useStatus'])) {
            $noArray[] = $this->findGlobalIdByUsedCount($conditions['useStatus']);
        }

        if (!empty($conditions['keywords']) && in_array($conditions['searchType'], array('course', 'user'))) {
            $noArray[] = $this->findGlobalIdsByKeyWords($conditions['searchType'], $conditions['keywords']);
            unset($conditions['keywords']);
        }

        $globalIds = array();

        for ($i = 0; $i < count($noArray); $i++) {
            if (empty($noArray[$i])) {
                $globalIds = array(0);
                break;
            }

            if ($i == 0) {
                $globalIds = $noArray[$i];
            } else {
                $globalIds = array_intersect($globalIds, $noArray[$i]);
            }

            if (empty($globalIds)) {
                $globalIds = array(0);
            }
        }

        $conditions['nos'] = implode(',', $globalIds);

        $conditions = array_filter($conditions, function ($value) {
            if ($value === '0') {
                return true;
            }

            return !empty($value);
        });

        unset($conditions['searchType']);
        unset($conditions['tags']);

        return $conditions;
    }

    protected function findGlobalIdByUsedCount($usedStatus)
    {
        if ($usedStatus == "used") {
            $conditions = array('startCount' => 1);
        }

        if ($usedStatus == "unused") {
            $conditions = array('endCount' => 1);
        }

        $files = $this->getUploadFileService()->searchFiles($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);

        if (!empty($files)) {
            return ArrayToolkit::column($files, 'globalId');
        }

        return array();
    }

    protected function findGlobalIdsByTags($tags)
    {
        $filesInTags = $this->getUploadFileTagService()->findByTagId($tags);
        $fileIds     = ArrayToolkit::column($filesInTags, 'fileId');
        $files       = $this->getUploadFileService()->findFilesByIds($fileIds);

        if (!empty($files)) {
            return ArrayToolkit::column($files, 'globalId');
        }

        return array();
    }

    protected function findGlobalIdsByKeyWords($searchType, $keywords)
    {
        if ($searchType == 'course') {
            $courses   = $this->getCourseService()->findCoursesByLikeTitle($keywords);
            $courseIds = ArrayToolkit::column($courses, 'id');

            if (empty($courseIds)) {
                $courseIds = array('0');
            }

            $courseMaterials = $this->getMaterialService()->searchMaterials(
                array('courseIds' => $courseIds),
                array('createdTime', 'DESC'),
                0, PHP_INT_MAX
            );

            $conditions        = array();
            $conditions['ids'] = ArrayToolkit::column($courseMaterials, 'fileId');

            $materials = $this->getUploadFileService()->searchFiles($conditions, array('createdTime', 'DESC'), 0, PHP_INT_MAX);
            $globalIds = ArrayToolkit::column($materials, 'globalId');

            return $globalIds;
        } elseif ($searchType == 'user') {
            $users = $this->getUserService()->searchUsers(array('nickname' => $keywords), array('id', 'desc'), 0, PHP_INT_MAX);

            $userIds = ArrayToolkit::column($users, 'id');

            $userIds    = empty($userIds) ? array(-1) : $userIds;
            $localFiles = $this->getUploadFileService()->searchFiles(
                array('createdUserIds' => $userIds, 'storage' => 'cloud'), array('createdTime', 'DESC'), 0, PHP_INT_MAX
            );
            $globalIds = ArrayToolkit::column($localFiles, 'globalId');

            return $globalIds;
        }

        return array();
    }

    public function edit($globalId, $fields)
    {
        if (empty($globalId)) {
            return false;
        }

        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);

        if (!empty($file)) {
            $result = $this->getUploadFileService()->update($file['id'], $fields);
            return array('success' => true);
        }

        $cloudFields = ArrayToolkit::parts($fields, array('name', 'tags', 'description'));
        return $this->getCloudFileImplementor()->updateFile($globalId, $cloudFields);
    }

    public function delete($globalId)
    {
        if (empty($globalId)) {
            return false;
        }

        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);

        if (!empty($file)) {
            $result = $this->getUploadFileService()->deleteFile($file['id']);
            return array('success' => true);
        }

        return $this->getCloudFileImplementor()->deleteFile(array('globalId' => $globalId));
    }

    public function batchDelete($globalIds)
    {
        if (empty($globalIds)) {
            return false;
        }

        foreach ($globalIds as $globalId) {
            $this->delete($globalId);
        }
    }

    public function getByGlobalId($globalId)
    {
        return $this->getCloudFileImplementor()->getFileByGlobalId($globalId);
    }

    public function player($globalId)
    {
        return $this->getCloudFileImplementor()->player($globalId);
    }

    public function download($globalId)
    {
        return $this->getCloudFileImplementor()->download($globalId);
    }

    public function reconvert($globalId, $options = array())
    {
        $this->getCloudFileImplementor()->reconvert($globalId, $options);
        $file = $this->getUploadFileService()->getThinFileByGlobalId($globalId);

        if (empty($file)) {
            $file = array('globalId' => $globalId);
        }

        return $this->getCloudFileImplementor()->getFile($file);
    }

    public function getDefaultHumbnails($globalId)
    {
        return $this->getCloudFileImplementor()->getDefaultHumbnails($globalId);
    }

    public function getThumbnail($globalId, $options)
    {
        return $this->getCloudFileImplementor()->getThumbnail($globalId, $options);
    }

    public function getStatistics($options = array())
    {
        return $this->getCloudFileImplementor()->getStatistics($options);
    }

    public function synData($conditions)
    {
        return $this->getCloudFileImplementor()->synData($conditions);
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
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

    protected function getCloudFileImplementor()
    {
        return $this->createService('File.CloudFileImplementor');
    }

    protected function getMaterialService()
    {
        return $this->createService('Course.MaterialService');
    }
}
