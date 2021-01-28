<?php

namespace Biz\S2B2C\Service;

interface FileSourceService
{
    public function getFullFileInfo($file);

    public function player($globalId, $ssl = false);
}
