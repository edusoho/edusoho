<?php

namespace Topxia\Service\Taxonomy\Dao;

interface KnowledgeDao 
{
	public function getKnowledge($id);
	
	public function updateKnowledge($id, $fields);

	public function deleteKnowledge($id);

	public function createKnowledge($Knowledge);

	public function searchKnowledges($conditions, $sort, $start, $limit);

	public function findKnowledgeByCategoryId($categoryId);

	public function findKnowledgeByParentId($parentId);

	public function findKnowledgeByCode($code);
}