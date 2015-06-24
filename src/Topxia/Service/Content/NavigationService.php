<?php
namespace Topxia\Service\Content;

interface NavigationService
{
    public function getNavigation($id);

    /**
    * isOpen: null - all nav, 0 - closed nav, 1 - open nav
    */
    public function findNavigations($start, $limit, $isOpen = null);

    public function getNavigationsCount();

    /**
    * isOpen: null - all nav, 0 - closed nav, 1 - open nav
    */
    public function findNavigationsByType($type, $start, $limit, $isOpen = null);

    /**
    * isOpen: null - all nav, 0 - closed nav, 1 - open nav
    */
    public function getNavigationsTreeByType($type, $isOpen = null);

    /**
    * isOpen: null - all nav, 0 - closed nav, 1 - open nav
    */
    public function getNavigationsCountByType($type, $isOpen = null);

    /**
    * isOpen: null - all nav, 0 - closed nav, 1 - open nav
    */
    public function getNavigationsListByType($type, $isOpen = null);

    public function createNavigation($fields);

    public function updateNavigation($id, $fields);

    public function updateNavigationsSequenceByIds($ids);

    public function deleteNavigation($id);
}