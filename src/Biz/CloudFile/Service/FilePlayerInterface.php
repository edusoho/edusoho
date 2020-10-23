<?php

namespace Biz\CloudFile\Service;

interface FilePlayerInterface
{
    public function player($globalId, $ssl = false);
}
