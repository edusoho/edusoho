<?php
namespace Custom\Service\Taxonomy;

interface ColumnService
{
        public function getColumn($id);

        public function getColumnByName($name);

        // public function getTagByLikeName($name);

        public function findAllColumns($start, $limit);

        public function getAllColumnCount();

        // public function findTagsByIds(array $ids);

        public function findColumnsByNames(array $names);

        public function isColumnNameAvalieable($name, $exclude=null);

        public function addColumn(array $column);

        public function updateColumn($id, array $fields);

        public function deleteColumn($id);

          public function changeColumnAvatar($columnId, $filePath, array $options);
}

