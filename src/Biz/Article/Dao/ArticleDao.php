<?php

namespace Biz\Article\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ArticleDao extends GeneralDaoInterface
{
    public function getPrevious($categoryId, $createdTime);

    public function getNext($categoryId, $createdTime);

    public function findAll();

    public function findByIds(array $ids);

    public function searchByCategoryIds(array $categoryIds, $start, $limit);

    public function countByCategoryIds(array $categoryIds);

    public function waveArticle($id, $field, $diff);
}
