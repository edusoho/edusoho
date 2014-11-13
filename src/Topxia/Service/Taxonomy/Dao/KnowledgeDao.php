<?php

namespace Topxia\Service\Taxonomy\Dao;

interface KnowledgeDao 
{
	public function getKnowledge($id);
	
	public function createKnowledge($Knowledge);

	public function findKnowledgeByCategoryId($categoryId);

	public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);

	public function findKnowledgeByCode($code);
}