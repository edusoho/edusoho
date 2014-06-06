<?php

namespace Topxia\Service\MyGroup;

interface ThreadService
{
    public function publishthread($info);
    public function searchThread($id,$strat,$limit,$sort);
}