<?php


namespace Biz\Group\Service;

// TODO refactor. use Thread.
interface ThreadService
{
    public function getThread($id);

    public function searchThreads($conditions,$orderBy,$start, $limit);

    public function countThreads($conditions);

    public function searchThreadCollects($conditions,$orderBy,$start,$limit);

    public function countThreadCollects($conditions);

    public function searchPostsThreadIds($conditions,$orderBy,$start,$limit);

    public function countPostsThreadIds($conditions);
}