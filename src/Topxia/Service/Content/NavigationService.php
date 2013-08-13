<?php
namespace Topxia\Service\Content;

interface NavigationService
{
    public function getNavigationsCount();

    public function getNavigationsCountByType($type);

    public function findNavigations($start, $limit);
    
    public function findNavigationsByType($type, $start, $limit);

    public function createNavigation($fields);

    public function getNavigation($id);

    public function deleteNavigation($id);

    public function editNavigation($id, $fields);
}