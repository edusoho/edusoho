<?php

namespace Topxia\Service\Content\Dao;

interface NavigationDao
{

    public function getNavigation($id);

    public function addNavigation($navigation);

    public function updateNavigation($id, $fields);

    public function deleteNavigation($id);

    public function deleteNavigationByParentId($parentId);

    public function getNavigationsCount();
	/**
    * isOpen: null - all nav, 0 - closed nav, 1 - open nav
    */
    public function getNavigationsCountByType($type, $isOpen = null);

	/**
    * isOpen: null - all nav, 0 - closed nav, 1 - open nav
    */
    public function findNavigations($start, $limit, $isOpen = null);
	/**
    * isOpen: null - all nav, 0 - closed nav, 1 - open nav
    */
    public function findNavigationsByType($type, $start, $limit, $isOpen = null);
    
}