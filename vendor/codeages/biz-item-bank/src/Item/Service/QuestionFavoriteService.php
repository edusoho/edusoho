<?php

namespace Codeages\Biz\ItemBank\Item\Service;

interface QuestionFavoriteService
{
    public function create($questionFavorite);

    public function delete($id);

    public function deleteByQuestionFavorite($questionFavorite);

    public function search($conditions, $orderBys, $start, $limit, $columns = []);

    public function count($conditions);
}
