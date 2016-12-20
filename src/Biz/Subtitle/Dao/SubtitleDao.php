<?php

namespace Biz\Subtitle\Dao;

interface SubtitleDao
{
    public function findSubtitlesByMediaId($mediaId);
}
