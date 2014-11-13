<?php
namespace Topxia\Service\Taxonomy;

interface KnowledgeService
{
    public function createKnowledge($knowledge);

    public function findKnowledgeByCategoryId($categoryId);

    public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);

    public function getKnowledgeTree($categoryId);

    public function isKnowledgeCodeAvaliable($code, $exclude);
}