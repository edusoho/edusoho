<?php

namespace Biz\CloudPlatform\Service;

interface SearchService
{
    public function notifyUpdate($params);

    public function notifyDelete($params);

    public function setCloudApi($node, $api);
}
