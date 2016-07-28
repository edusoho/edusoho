<?php
namespace Topxia\Service\CloudAttachment;

interface CloudAttachmentService
{
    public function searchFileCount($conditions);

    public function searchFiles($conditions, $sort, $start, $limit);
}
