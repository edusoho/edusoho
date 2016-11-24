<?php

namespace Topxia\Service\Subtitle\Dao;

interface SubtitleDao
{
    public function findSubtitlesByMediaId($mediaId);

    public function getSubtitle($id);
    
    public function addSubtitle($subtitle);

    public function deleteSubtitle($id);
}
