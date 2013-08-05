<?php
namespace Topxia\Service\Content;

interface NavigationService
{
    public function getNavigationsCount();
    
    public function getTopNavigationsCount();

    public function getFootNavigationsCount();

    public function findNavigations($start, $limit);
    
    public function findTopNavigations($start, $limit);

    public function findFootNavigations($start, $limit);

    public function createNavigation($fields);

    public function getNavigation($id);

    public function deleteNavigation($id);

    public function editNavigation($id, $fields);
}