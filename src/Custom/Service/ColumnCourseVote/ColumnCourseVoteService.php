<?php
namespace Custom\Service\ColumnCourseVote;

interface ColumnCourseVoteService
{
        // public function getColumn($id);

        // public function getColumnByName($name);

        // public function getColumnByLikeName($name);

        public function findAllCourseVote($start, $limit);

        public function getAllCourseVoteCount();

        // public function findColumnsByIds(array $ids);

        // public function findColumnsByNames(array $names);

        // public function isColumnNameAvalieable($name, $exclude=null);

        public function addColumnCourseVote(array $columnCourseVote);

        // public function updateColumn($id, array $fields);

        // public function deleteColumn($id);

        //   public function changeColumnAvatar($columnId, $filePath, array $options);
}

