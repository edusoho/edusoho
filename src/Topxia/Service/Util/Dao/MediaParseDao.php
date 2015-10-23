<?php

namespace Topxia\Service\Util\Dao;

interface MediaParseDao
{
    public function getMediaParse($id);

    public function findMediaParseByUuid($uuid);

    public function findMediaParseByHash($hash);

    public function addMediaParse(array $fields);

    public function updateMediaParse($id, array $fields);
}