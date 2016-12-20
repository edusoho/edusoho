<?php


namespace Biz\Course\Service;

// TODO refactor. use Thread.
interface ThreadService
{
    public function searchThreads($conditions, $sort, $start, $limit);

    public function countThreads($conditions);

}