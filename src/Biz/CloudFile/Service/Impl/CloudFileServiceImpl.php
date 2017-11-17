<?php

namespace Biz\CloudFile\Service\Impl;

use Biz\BaseService;
use Biz\File\Service\FileImplementor;
use Biz\File\Service\UploadFileService;
use Biz\File\Service\UploadFileTagService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudFile\Service\CloudFileService;
use Topxia\Service\Common\ServiceKernel;
use QiQiuYun\SDK\Service\ResourceService;

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
        $conditions = array_filter($conditions, function ($condition) {
            return !empty($condition);
        });

        if (!empty($conditions['tags'])) {
            $this->findGlobalIdsByTags($conditions);
        }

        if (!empty($conditions['useStatus'])) {
            $this->findGlobalIdByUsedCount($conditions);
        }

        if (!empty($conditions['keywords'])) {
            $this->findGlobalIdsByKeyWords($conditions);
        }

        return $conditions;
    }

    protected function findGlobalIdByUsedCount(&$conditions)
    {
        if ($conditions['useStatus'] == 'used') {
            $conditions['startCount'] = 1;
        } else {
            $conditions['endCount'] = 1;
        }
        unset($conditions['useStatus']);
    }

    protected function findGlobalIdsByTags(&$conditions)
    {
        $filesInTags = $this->getUploadFileTagService()->findByTagId($conditions['tags']);
        $fileIds = ArrayToolkit::column($filesInTags, 'fileId');
        $conditions['ids'] = empty($fileIds) ? array(-1) : $fileIds;
        unset($conditions['tags']);
    }

    protected function findGlobalIdsByKeyWords(&$conditions)
    {
        $searchType = $conditions['searchType'];
        $keywords = $conditions['keywords'];

        if (!in_array($conditions['searchType'], array('course', 'title', 'user'))) {
            return;
        }
        $unavailableSearch = isset($conditions['ids']) && in_array(-1, $conditions['ids']);
        if ($unavailableSearch) {
            return;
        }
        if ($searchType == 'course') {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($keywords);
            if (empty($courseSets)) {
                $conditions['ids'] = array(-1);
            } else {
                $courseSetIds = ArrayToolkit::column($courseSets, 'id');
                $courseMaterials = $this->getMaterialService()->searchMaterials(
                    array('courseSetIds' => $courseSetIds),
                    array('createdTime' => 'DESC'),
                    0,
                    PHP_INT_MAX
                );

                $fileIds = ArrayToolkit::column($courseMaterials, 'fileId');
                $fileIds = empty($fileIds) ? array(-1) : $fileIds;
                if (isset($conditions['ids'])) {
                    $conditions['ids'] = array_merge($conditions['ids'], $fileIds);
                } else {
                    $conditions['ids'] = $fileIds;
                }
            }

            $conditions['ids'] = array_unique($conditions['ids']);
        } elseif ($searchType == 'user') {
            $users = $this->getUserService()->searchUsers(array('nickname' => $keywords), array('id' => 'DESC'), 0, PHP_INT_MAX);

            $userIds = ArrayToolkit::column($users, 'id');

            $conditions['createdUserIds'] = empty($userIds) ? array(-1) : $userIds;
        } else {
            $conditions['filename'] = $conditions['keywords'];
        }

        unset($conditions['searchType'], $conditions['keywords']);
    }

    public function edit($globalId, $fields)
    {
        if (empty($globalId)) {
            return false;
        }

        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);

        if (!empty($file)) {
            $this->getUploadFileService()->update($file['id'], $fields);

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
            $this->getUploadFileService()->deleteFile($file['id']);

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

        return true;
    }

    public function getByGlobalId($globalId)
    {
        return $this->getCloudFileImplementor()->getFileByGlobalId($globalId);
    }

    public function player($globalId, $ssl = false)
    {
        $result = $this->getCloudFileImplementor()->player($globalId, $ssl);
        if (!empty($result) && is_array($result)) {
            $result['token'] = $this->getResourceService()->generatePlayToken($globalId);
        }

        return $result;
    }

    public function download($globalId)
    {
        return $this->getCloudFileImplementor()->download($globalId);
    }

    public function reconvert($globalId, $options = array())
    {
        $this->getCloudFileImplementor()->reconvert($globalId, $options);
        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);

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

    public function deleteCloudMP4Files($callback)
    {
        return $this->getCloudFileImplementor()->deleteMP4Files($callback);
    }

    protected function getResourceService()
    {
        $storage = $this->getSettingService()->get('storage', array());
        $config = array(
            'access_key' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
            'secret_key' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
        );

        return new ResourceService($config);
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return UploadFileTagService
     */
    protected function getUploadFileTagService()
    {
        return $this->createService('File:UploadFileTagService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return FileImplementor
     */
    protected function getCloudFileImplementor()
    {
        return $this->createService('File:CloudFileImplementor');
    }

    protected function getMaterialService()
    {
        return ServiceKernel::instance()->createService('Course:MaterialService');
    }
}
