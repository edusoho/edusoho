<?php

namespace Biz\Subtitle\Service;

interface SubtitleService
{
    public function findSubtitlesByMediaId($mediaId, $ssl = false);

    public function getSubtitle($id);

    public function addSubtitle($subtitle);

    public function deleteSubtitle($id);
}
