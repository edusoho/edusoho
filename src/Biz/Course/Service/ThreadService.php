<?php


namespace Biz\Course\Service;


interface ThreadService
{
    public function searchThreads($conditions, $sort, $start, $limit);

    public function countThreads($conditions);

}