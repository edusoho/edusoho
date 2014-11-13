<?php
namespace Topxia\Service\Taxonomy;

interface KnowledgeService
{

	public function getKnowledge($id);

	public function updateKnowledge($id, $fields);
	
	public function deleteKnowledge($id);
		
    public function createKnowledge($knowledge);

    public function findKnowledgeByCategoryId($categoryId);

    public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);

    public function getKnowledgeTree($categoryId);

    public function isKnowledgeCodeAvaliable($code, $exclude);
}