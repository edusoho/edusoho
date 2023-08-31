<?php

namespace Biz\Search\Strategy;

interface LocalSearchStrategy
{
    public function buildSearchConditions($keyword, $filter);

    public function count();

    public function search($start, $limit);
}
