<?php

namespace Biz\File\Service;

interface UploadFileShareHistoryService
{
    public function getShareHistory($id);

    public function addShareHistory($sourceUserId, $targetUserId, $isActive);

    public function findShareHistory($sourceUserId);

    public function searchShareHistoryCount($conditions);

    public function searchShareHistories($conditions, $orderBy, $start, $limit);
}
