<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Service\Taxonomy\KnowledgeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class KnowledgeServiceImpl extends BaseService implements KnowledgeService
{
	public function addKnowledge($knowledge)
	{
		
	}

	public function findKnowledgeByCategoryId($categoryId)
	{
		return $this->getKnowledgeDao()->findKnowledgeByCategoryId($categoryId);
	}

	public function getKnowledgeTree($categoryId)
	{
	    $category = $this->getCategoryService()->getCategory($categoryId);
	    if (empty($category)) {
	        throw $this->createServiceException("分类Category #{$categoryId}，不存在");
	    }
	    $prepare = function($knowledges) {
	        $prepared = array();
	        foreach ($knowledges as $knowledge) {
	            if (!isset($prepared[$knowledge['parentId']])) {
	                $prepared[$knowledge['parentId']] = array();
	            }
	            $prepared[$knowledge['parentId']][] = $knowledge;
	        }
	        return $prepared;
	    };

	    $knowledges = $prepare($this->findKnowledgeByCategoryId($categoryId));

	    $tree = array();
	    $this->makeKnowledgeTree($tree, $knowledges, 0);

	    return $tree;
	}

	private function makeKnowledgeTree(&$tree, &$knowledges, $parentId)
	{
	    static $depth = 0;
	    static $leaf = false;
	    if (isset($knowledges[$parentId]) && is_array($knowledges[$parentId])) {
	        foreach ($knowledges[$parentId] as $knowledge) {
	            $depth++;
	            $knowledge['depth'] = $depth;
	            $tree[] = $knowledge;
	            $this->makeKnowledgeTree($tree, $knowledges, $knowledge['id']);
	            $depth--;
	        }
	    }
	    return $tree;
	}

	protected function getKnowledgeDao()
	{
		return $this->createDao('Taxonomy.KnowledgeDao');
	}

	protected function getCategoryService()
	{
		return $this->createService('Taxonomy.CategoryService');
	}
}