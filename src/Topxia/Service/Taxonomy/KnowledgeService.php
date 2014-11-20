<?php
namespace Topxia\Service\Taxonomy;

interface KnowledgeService
{

	public function getKnowledge($id);

	public function updateKnowledge($id, $fields);
	
	public function deleteKnowledge($id);
		
    public function createKnowledge($knowledge);

    public function searchKnowledges($conditions, $sort, $start, $limit);

    public function sort($id, $parentId, $seq);

    public function findKnowledgeByCategoryId($categoryId);

    public function findKnowledgeByParentId($parentId);

    public function findNodesData($parentId, $query);

    public function isKnowledgeCodeAvaliable($code, $exclude);
}