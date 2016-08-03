<?php

namespace Topxia\Service\Attachment\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Attachment\AttachmentService;

class AttachmentServiceImpl extends BaseService implements AttachmentService
{
    const TYPE = 'attachment';

    public function creates(array $attachments)
    {
        $service = $this;
        try {
            $this->getFileUsedDao()->getConnection()->beginTransaction();
            array_walk($attachments, function ($attachment) use ($service) {
                $conditions = array(
                    'type'       => AttachmentServiceImpl::TYPE,
                    'targetType' => $attachment['targetType'],
                    'targetId'   => $attachment['targetId'],
                    'fileId'     => $attachment['fileId']
                );

                $existed = $service->getFileUsedDao()->search($conditions, array('createdTime', 'DESC'), 0, 1);

                if (!empty($existed)) {
                    return;
                }

                $service->create($attachment);
            });
            $this->getFileUsedDao()->getConnection()->commit();
            return true;
        } catch (\Exception $e) {
            $this->getFileUsedDao()->getConnection()->rollback();
            return false;
        }
    }

    public function create($attachment)
    {
        $attachment['type']        = self::TYPE;
        $attachment['createdTime'] = time();

        $attachment = $this->getFileUsedDao()->create($attachment);
        $this->bindFile($attachment);
        return $attachment;
    }

    public function proxyCreate($targetObj, $attachment)
    {
        $fileIds    = empty($attachment['fileIds']) ? array() : explode(",", $attachment['fileIds']);
        $targetType = $attachment['targetType'];
        if (empty($fileIds)) {
            return false;
        }
        $attachments = array_map(function ($fileId) use ($targetObj, $targetType) {
            $attachment = array(
                'fileId'     => $fileId,
                'targetType' => $targetType,
                'targetId'   => $targetObj['id']
            );
            return $attachment;
        }, $fileIds);
        $this->creates($attachments);

        $files = $this->getUploadFileService()->findFilesByIds($fileIds);
        foreach ($files as $file) {
            $this->getUploadFileService()->update($file['id'], array('useType' => $targetType, 'usedCount' => $file['usedCount'] + 1));
        }
    }

    public function findByTargetTypeAndTargetId($targetType, $targetId)
    {
        $conditions = array(
            'type'       => self::TYPE,
            'targetType' => $targetType,
            'targetId'   => $targetId
        );

        $limit       = $this->getFileUsedDao()->count($conditions);
        $attachments = $this->getFileUsedDao()->search($conditions, array('createdTime', 'DESC'), 0, $limit);
        $this->bindFiles($attachments);
        return $attachments;
    }

    public function get($id)
    {
        $attachment = $this->getFileUsedDao()->get($id);
        $this->bindFile($attachment);
        return $attachment;
    }

    public function delete($id)
    {
        $attachment = $this->getFileUsedDao()->get($id);
        $file       = $this->getUploadFileService()->getFile($attachment['fileId']);
        if (empty($file)) {
            $this->createNotFoundException("该附件不存在,或已被删除");
        }
        $user = $this->getCurrentUser();
        if (!$user->isLogin() || $file['createdUserId'] != $user['id'] || !$user->isAdmin()) {
            $this->createAccessDeniedException("您无全删除改附件");
        }

        try {
            $this->getFileUsedDao()->getConnection()->beginTransaction();

            $this->getUploadFileService()->deleteFile($file['id']);
            $this->getFileUsedDao()->delete($id);

            $this->getFileUsedDao()->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getFileUsedDao()->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * Impure Function
     * attachment 增加key file
     * @param $attachment
     */
    protected function bindFile(&$attachment)
    {
        $file = $this->getUploadFileService()->getFile($attachment['fileId']);
        if (is_null($file)) {
            $attachment['file'] = array();
        } else {
            $attachment['file'] = $file;
        }
    }

    /**
     * Impure Function
     * 每个attachment 增加key file
     * @param array $attachments
     */
    protected function bindFiles(array &$attachments)
    {
        $files = $this->getUploadFileService()->findFilesByIds(ArrayToolkit::column($attachments, 'fileId'), 1);
        $files = ArrayToolkit::index($files, 'id');
        array_walk($attachments, function (&$attachment) use ($files) {
            if (isset($files[$attachment['fileId']])) {
                $attachment['file'] = $files[$attachment['fileId']];
            } else {
                $attachment['file'] = array();
            }
        });
    }

    protected function getFileUsedDao()
    {
        return $this->createDao('File.FileUsedDao');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }
}
