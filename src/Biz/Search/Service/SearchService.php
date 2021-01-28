<?php

namespace Biz\Search\Service;

interface SearchService
{
    public function cloudSearch($type, $condtions);

    public function refactorAllDocuments();

    public function applySearchAccount($callbackRouteUrl);

    public function setCloudApi($node, $api);
}
