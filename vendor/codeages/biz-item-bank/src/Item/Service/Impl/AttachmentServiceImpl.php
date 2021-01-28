<?php

namespace Codeages\Biz\ItemBank\Item\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\ErrorCode;
use Codeages\Biz\ItemBank\Item\Dao\AttachmentDao;
use Codeages\Biz\ItemBank\Item\Exception\ItemException;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
use Firebase\JWT\JWT;

class AttachmentServiceImpl extends BaseService implements AttachmentService
{
    public function getAttachment($id)
    {
        return $this->getAttachmentDao()->get($id);
    }

    public function getAttachmentByGlobalId($globalId)
    {
        return $this->getAttachmentDao()->getByGlobalId($globalId);
    }

    public function findAttachmentsByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->getAttachmentDao()->findByTargetIdAndTargetType($targetId, $targetType);
    }

    public function findAttachmentsByTargetIdsAndTargetType($targetIds, $targetType)
    {
        return $this->getAttachmentDao()->findByTargetIdsAndTargetType($targetIds, $targetType);
    }

    public function createAttachment($attachment)
    {
        $attachment = $this->validateAttachment($attachment);

        $attachment['status'] = 'uploading';
        $attachment['created_user_id'] = empty($this->biz['user']['id']) ? 0 : $this->biz['user']['id'];

        return $this->getAttachmentDao()->create($attachment);
    }

    public function updateAttachment($attachmentId, $fields)
    {
        $attachment = $this->getAttachment($attachmentId);
        if (empty($attachment)) {
            throw new ItemException('attachment not exist', ErrorCode::ITEM_ATTACHMENT_NOTFOUND());
        }

        $fields = $this->validateAttachment($fields);

        return $this->getAttachmentDao()->update($attachmentId, $fields);
    }

    public function finishUpload($attachmentId)
    {
        $attachment = $this->getAttachment($attachmentId);
        if (empty($attachment)) {
            throw new ItemException('attachment not exist', ErrorCode::ITEM_ATTACHMENT_NOTFOUND());
        }

        return $this->getAttachmentDao()->update($attachmentId, ['status' => 'finish']);
    }

    public function deleteAttachment($attachmentId)
    {
        $attachment = $this->getAttachment($attachmentId);
        if (empty($attachment)) {
            throw new ItemException('attachment not exist', ErrorCode::ITEM_ATTACHMENT_NOTFOUND());
        }

        $result = $this->getAttachmentDao()->delete($attachmentId);

        $this->dispatch('item.attachment.delete', $attachment);

        return $result;
    }

    public function batchDeleteAttachment($conditions)
    {
        $count = $this->getAttachmentDao()->count($conditions);
        $attachments = $this->getAttachmentDao()->search($conditions, [], 0, $count);
        if (empty($attachments)) {
            return array();
        }

        $result = $this->getAttachmentDao()->batchDelete(['ids' => ArrayToolkit::column($attachments, 'id')]);

        $this->dispatch('item.attachment.batch_delete', $attachments);

        return $result;
    }

    public function makeToken($user, $accessKey, $secretKey, $ttl = 86400)
    {
        $metas = "{$user['uuid']}|attachment|{$user['id']}|private";
        $payload = array(
            'iss' => 'EduSoho',
            'aud' => 'EduSoho',
            'exp' => time() + $ttl,
            'metas' => $metas,
        );

        return JWT::encode($payload, md5($accessKey.$secretKey), 'HS256');
    }

    public function parseToken($token, $accessKey, $secretKey)
    {
        if (empty($token)) {
            return null;
        }
        $payload = JWT::decode($token, md5($accessKey.$secretKey), ['HS256']);
        $metas = $payload->metas;
        list($uuid, $targetType, $targetId, $bucket) = explode('|', $metas);

        return [
            'uuid' => $uuid,
            'targetType' => $targetType,
            'targetId' => $targetId,
            'bucket' => $bucket,
        ];
    }

    public function batchUpdate($attachments)
    {
        if (empty($attachments)) {
            return false;
        }

        return $this->getAttachmentDao()->batchUpdate(ArrayToolkit::column($attachments, 'id'), $attachments, 'id');
    }

    protected function validateAttachment($attachment)
    {
        return $this->getValidator()->validate($attachment, [
            'file_name' => [['lengthBetween', 1, 1024]],
            'ext' => [['lengthBetween', 1, 12]],
            'hash_id' => [['lengthBetween', 1, 128]],
            'target_type' => [['in', [self::ITEM_TYPE, self::QUESTION_TYPE, self::ANSWER_TYPE]]],
            'module' => [['in', [self::MATERIAL_MODULE, self::STEM_MODULE, self::ANALYSIS_MODULE, self::ANSWER_MODULE]]],
            'target_id' => [['min', 0]],
            'global_id' => [['lengthBetween', 1, 32]],
            'size' => [],
            'file_type' => [],
            'convert_status' => [],
            'audio_convert_status' => [],
            'mp4_convert_status' => [],
        ]);
    }

    /**
     * @return AttachmentDao
     */
    protected function getAttachmentDao()
    {
        return $this->biz->dao('ItemBank:Item:AttachmentDao');
    }
}
