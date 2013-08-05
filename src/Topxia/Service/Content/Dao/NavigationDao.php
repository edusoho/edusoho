<?php

namespace Topxia\Service\Content\Dao;

interface NavigationDao
{

    public function getNavigation($id);

    public function addNavigation($navigation);

    public function updateNavigation($id, $fields);

    public function deleteNavigation($id);

    public function getNavigationsCount();

    public function getTopNavigationsCount();

    public function getFootNavigationsCount();

    public function findNavigations($start, $limit);
    
    public function findTopNavigations($start, $limit);
    
    public function findFootNavigations($start, $limit);

}