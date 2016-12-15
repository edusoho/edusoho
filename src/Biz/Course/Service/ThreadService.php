<?php


namespace Biz\Course\Service;


interface ThreadService
{
    public function countThreads($conditions);

    public function searchThreads($conditions, $sort, $start, $limit);

}