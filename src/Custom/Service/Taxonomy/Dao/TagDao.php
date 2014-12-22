<?php
namespace Custom\Service\Taxonomy\Dao;

interface TagDao
{
    public function findAllTags($start, $limit);
}