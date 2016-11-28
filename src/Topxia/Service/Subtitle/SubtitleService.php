<?php

namespace Topxia\Service\Subtitle;

interface SubtitleService
{
    public function findSubtitlesByMediaId($mediaId);

    public function getSubtitle($id);
    
    public function addSubtitle($subtitle);

    public function deleteSubtitle($id);
    
}