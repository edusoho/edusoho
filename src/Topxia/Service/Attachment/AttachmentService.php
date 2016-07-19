<?php

namespace Topxia\Service\Attachment;


interface AttachmentService
{
    public function creates(array $attachments);
    public function create($attachment);
    public function findByTargetTypeAndTargetId($targetType, $targetId);
    public function get($id);
}