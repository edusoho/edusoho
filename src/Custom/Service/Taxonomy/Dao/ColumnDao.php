<?php
namespace Custom\Service\Taxonomy\Dao;

interface ColumnDao
{
	public function addColumn(array $column);

	public function updateColumn($id, array $fields);

    public function findColumnsByIds(array $ids);

    public function findColumnsByNames(array $names);

    public function findAllColumns($start, $limit);

    public function getColumn($id);

    public function getColumnByName($name);

    public function getColumnByLikeName($name);

    public function findAllColumnsCount();

    public function deleteColumn($id);
}