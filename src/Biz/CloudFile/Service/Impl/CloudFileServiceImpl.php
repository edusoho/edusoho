<?php

namespace Biz\CloudFile\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TimeMachine;
use Biz\BaseService;
use Biz\CloudFile\Service\CloudFileService;
use Biz\File\Service\FileImplementor;
use Biz\File\Service\UploadFileService;
use Biz\File\Service\UploadFileTagService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;

class CloudFileServiceImpl extends BaseService implements CloudFileService
{
    public function search($conditions, $start, $limit)
    {
        if (empty($conditions['resType'])) {
            $conditions['noTargetType'] = 'attachment';
            $conditions = $this->filterConditions($conditions);
        } else {
            $conditions['targetType'] = $conditions['resType'];
        }

        $result['count'] = $this->getUploadFileService()->countCloudFilesFromLocal($conditions);
        $result['data'] = $this->getUploadFileService()->searchCloudFilesFromLocal($conditions, ['id' => 'DESC'], $start, $limit);

        $createdUserIds = ArrayToolkit::column($result['data'], 'createdUserId');
        $result['createdUsers'] = $this->getUserService()->findUsersByIds($createdUserIds);

        $result['data'] = array_map(function ($file) {
            $file['no'] = $file['globalId'];
            $file['processStatus'] = empty($file['processStatus']) ? 'none' : $file['processStatus'];

            return $file;
        }, $result['data']);

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
        if ('used' == $conditions['useStatus']) {
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
        $conditions['ids'] = empty($fileIds) ? [-1] : $fileIds;
        unset($conditions['tags']);
    }

    protected function findGlobalIdsByKeyWords(&$conditions)
    {
        $searchType = $conditions['searchType'];
        $keywords = $conditions['keywords'];

        if (!in_array($conditions['searchType'], ['course', 'title', 'user'])) {
            return;
        }
        $unavailableSearch = isset($conditions['ids']) && in_array(-1, $conditions['ids']);
        if ($unavailableSearch) {
            return;
        }
        if ('course' == $searchType) {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($keywords);
            if (empty($courseSets)) {
                $conditions['ids'] = [-1];
            } else {
                $courseSetIds = ArrayToolkit::column($courseSets, 'id');
                $courseMaterials = $this->getMaterialService()->searchMaterials(
                    ['courseSetIds' => $courseSetIds],
                    ['createdTime' => 'DESC'],
                    0,
                    PHP_INT_MAX
                );
                $fileIds = ArrayToolkit::column($courseMaterials, 'fileId');
                $fileIds = empty($fileIds) ? [-1] : $fileIds;
                if (isset($conditions['ids'])) {
                    $conditions['ids'] = array_merge($conditions['ids'], $fileIds);
                } else {
                    $conditions['ids'] = $fileIds;
                }
            }

            $conditions['ids'] = array_unique($conditions['ids']);
        } elseif ('user' == $searchType) {
            $users = $this->getUserService()->searchUsers(['nickname' => $keywords], ['id' => 'DESC'], 0, PHP_INT_MAX);

            $userIds = ArrayToolkit::column($users, 'id');

            $conditions['createdUserIds'] = empty($userIds) ? [-1] : $userIds;
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

            return ['success' => true];
        }

        $cloudFields = ArrayToolkit::parts($fields, ['name', 'tags', 'description']);

        return $this->getCloudFileImplementor()->updateFile($globalId, $cloudFields);
    }

    public function delete($globalId)
    {
        $user = $this->getCurrentUser();
        if (empty($globalId)) {
            return false;
        }

        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);
        $attachmentFile = $this->getAttachmentService()->getAttachmentByGlobalId($globalId);

        if (!empty($file)) {
            $this->getUploadFileService()->deleteFile($file['id']);

            return ['success' => true];
        }

        if (!empty($attachmentFile)) {
            $this->getAttachmentService()->updateAttachment($attachmentFile['id'], ['status' => 'delete']);
            $this->getLogService()->info('question_bank', 'delete_attachment', "管理员 {$user['nickname']}删除了附件《{$attachmentFile['file_name']}》");

            return ['success' => true];
        }

        return $this->getCloudFileImplementor()->deleteFile(['globalId' => $globalId]);
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
        $file = $this->getCloudFileImplementor()->getFileByGlobalId($globalId);
        $questionFile = empty($file['id']) ? $this->getByGlobalIdFromItemAttachment($globalId) : [];
        if(!empty($questionFile)){
            $file['type'] = $questionFile['file_type'];
            $file['convertStatus'] = "success";
            $file['globalId'] = $file['no'] = $questionFile['global_id'];
            $file['id'] = $questionFile['id'];
            $file['itemAttachement'] = 1;
        }

        return $file;
    }

    public function getByGlobalIdFromItemAttachment($globalId)
    {
        return $this->getAttachmentDao()->getByGlobalId($globalId);
    }

    public function player($globalId, $ssl = false)
    {
        $result = $this->getCloudFileImplementor()->player($globalId, $ssl);
        if (!empty($result) && is_array($result)) {
            $result['token'] = $this->biz['qiQiuYunSdk.play']->makePlayToken($globalId);
        }

        return $result;
    }

    public function download($globalId, $ssl = false)
    {
        $this->getLogService()->info('upload_file', 'download', "下载文件 globalId #{$globalId}");

        return $this->getCloudFileImplementor()->download($globalId, $ssl);
    }

    public function reconvert($globalId, $options = [])
    {
        $this->getCloudFileImplementor()->reconvert($globalId, $options);
        $file = $this->getUploadFileService()->getFileByGlobalId($globalId);

        if (empty($file)) {
            $file = ['globalId' => $globalId];
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

    public function getStatistics($options = [])
    {
        return $this->getCloudFileImplementor()->getStatistics($options);
    }

    public function deleteCloudMP4Files($userId, $callback)
    {
        $tokenFields = [
            'userId' => $userId,
            'duration' => TimeMachine::ONE_MONTH,
            'times' => 1,
        ];
        $token = $this->getTokenService()->makeToken('mp4_delete.callback', $tokenFields);

        $callback = $callback.'&token='.$token['token'];

        return $this->getCloudFileImplementor()->deleteMP4Files($callback);
    }

    public function hasMp4Video()
    {
        $conditions = [
            'mcStatus' => 'yes',
            'page' => 1,
            'start' => 0,
            'limit' => 1,
        ];
        $result = $this->getCloudFileImplementor()->search($conditions);

        if (!empty($result['data'])) {
            return true;
        }

        return false;
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

    /**
     * @return \Biz\Course\Service\MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getAttachmentService()
    {
        return $this->createService('ItemBank:Item:AttachmentService');
    }

    protected function getAttachmentDao()
    {
        return $this->createDao('ItemBank:Item:AttachmentDao');
    }
}
