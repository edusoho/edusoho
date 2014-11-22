<?php
namespace Topxia\Service\Taxonomy;

interface KnowledgeService
{
	public function getKnowledge($id);

	public function updateKnowledge($id, $fields);
	
	public function deleteKnowledge($id);
		
    public function createKnowledge($knowledge);

    public function sort($id, $parentId, $seq);

    public function findKnowledgeByIds(array $ids);

    public function searchKnowledge($conditions,$orderBys,$start,$limit);

    public function findKnowledgeByCategoryId($categoryId);

    public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);

    public function findNodesData($categoryId, $parentId);

    public function isKnowledgeCodeAvaliable($code, $exclude);
}