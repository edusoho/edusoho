<?php

namespace Codeages\Biz\ItemBank\Item\Service;

interface AttachmentService
{
    const MATERIAL_MODULE = 'material';

    const STEM_MODULE = 'stem';

    const ANALYSIS_MODULE = 'analysis';

    const ANSWER_MODULE = 'answer';

    const ITEM_TYPE = 'item';

    const QUESTION_TYPE = 'question';

    const ANSWER_TYPE = 'answer';

    public function getAttachment($id);

    public function getAttachmentByGlobalId($globalId);

    public function findAttachmentsByTargetIdAndTargetType($targetId, $targetType);

    public function findAttachmentsByTargetIdsAndTargetType($targetIds, $targetType);

    public function createAttachment($attachment);

    public function finishUpload($attachmentId);

    public function updateAttachment($attachmentId, $fields);

    public function deleteAttachment($attachmentId);

    public function batchDeleteAttachment($conditions);

    public function makeToken($user, $accessKey, $secretKey, $ttl = 86400);

    public function parseToken($token, $accessKey, $secretKey);

    public function batchUpdate($attachments);
}
