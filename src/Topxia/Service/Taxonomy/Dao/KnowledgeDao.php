<?php

namespace Topxia\Service\Taxonomy\Dao;

interface KnowledgeDao 
{
	public function addKnowledge($Knowledge);

	public function findKnowledgeByCategoryId($categoryId);

	public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);
}