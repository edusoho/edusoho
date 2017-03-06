<?php

namespace Biz\Content\Service;

interface NavigationService
{
    public function getNavigation($id);

    public function findNavigations($start, $limit);

    public function getNavigationsCount();

    public function findNavigationsByType($type, $start, $limit);

    public function getOpenedNavigationsTreeByType($type);

    public function getNavigationsCountByType($type);

    public function getNavigationsListByType($type);

    public function createNavigation($fields);

    public function updateNavigation($id, $fields);

    public function updateNavigationsSequenceByIds($ids);

    public function deleteNavigation($id);

    public function searchNavigationCount($conditions);

    public function searchNavigations($conditions, $orderBy, $start, $limit);
}
