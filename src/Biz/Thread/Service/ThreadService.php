<?php


namespace Biz\Thread\Service;


interface ThreadService
{
    public function searchThreads($conditions, $sort, $start, $limit);

    public function countThread($conditions);
}