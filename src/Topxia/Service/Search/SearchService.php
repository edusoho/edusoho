<?php
namespace Topxia\Service\Search;

interface SearchService
{
	
    public function cloudSearch($type, $condtions);

    public function refactorAllDocuments();

    public function applySearchAccount($callbackRouteUrl);

}
