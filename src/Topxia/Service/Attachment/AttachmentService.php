<?php

namespace Topxia\Service\Attachment;


interface AttachmentService
{
    public function get($id);

    public function creates(array $attachments);

    public function create($attachment);

    public function delete($id);

    public function findByTargetTypeAndTargetId($targetType, $targetId);
}