<?php

namespace Topxia\Service\Activity\Dao;

interface ActivityDao
{

    public function getActivity($id);

    public function findActivitysByIds(array $ids);

	public function searchActivitys($conditions, $orderBy, $start, $limit);

	public function searchActivityCount($conditions);

    public function addActivity($activity);

    public function updateActivity($id, $fields);

    public function deleteActivity($id);

}