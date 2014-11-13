<?php
namespace Topxia\Service\Taxonomy;

interface KnowledgeService
{
    public function addKnowledge($knowledge);

    public function findKnowledgeByCategoryId($categoryId);

    public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);

    public function getKnowledgeTree($categoryId);
}