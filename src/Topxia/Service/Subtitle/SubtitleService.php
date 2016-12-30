<?php

namespace Topxia\Service\Subtitle;

interface SubtitleService
{
    public function findSubtitlesByMediaId($mediaId, $ssl = false);

    public function getSubtitle($id);
    
    public function addSubtitle($subtitle);

    public function deleteSubtitle($id);
    
}