<?php
namespace Biz\Question\Service;

interface QuestionService
{
    public function get($id);

    public function create($fields);

    public function update($id, $fields);

    public function delete($id);

    public function findQuestionsByIds(array $ids);

    public function findQuestionsByParentId($id);

    public function search($conditions, $sort, $start, $limit);

    public function searchCount($conditions);

}
