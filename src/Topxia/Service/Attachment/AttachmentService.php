<?php

namespace Topxia\Service\Attachment;

interface AttachmentService
{
    public function get($id);

    public function creates(array $attachments);

    public function create($attachment);
    /**
     * 代理目标，并创建其附件
     * @param  [type] $targetObject   [description]
     * @param  [type] $attachment     [description]
     * @return [type] [description]
     */
    public function proxyCreate($targetObject, $attachment);

    public function delete($id);

    public function findByTargetTypeAndTargetId($targetType, $targetId);
}
