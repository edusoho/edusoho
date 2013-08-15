<?php

namespace Topxia\Service\Content\Dao;

interface NavigationDao
{

    public function getNavigation($id);

    public function addNavigation($navigation);

    public function updateNavigation($id, $fields);

    public function deleteNavigation($id);

    public function getNavigationsCount();

    public function getNavigationsCountByType($type);

    public function findNavigations($start, $limit);
    
    public function findNavigationsByType($type, $start, $limit);
    
}