<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Service\Taxonomy\KnowledgeService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class KnowledgeServiceImpl extends BaseService implements KnowledgeService
{
	public function getKnowledge($id)
	{
		if (empty($id)) {
		    return null;
		}
		return $this->getKnowledgeDao()->getKnowledge($id);
	}

	public function createKnowledge($knowledge)
	{
		$knowledge = ArrayToolkit::parts($knowledge, array('description','name', 'code', 'weight', 'categoryId', 'parentId', 'isVisible'));

		if (!ArrayToolkit::requireds($knowledge, array('name', 'code', 'weight', 'categoryId', 'parentId'))) {
		    throw $this->createServiceException("缺少必要参数，，添加分类失败");
		}

		$this->filterKnowledgeFields($knowledge);
		$knowledge = $this->getKnowledgeDao()->createKnowledge($knowledge);

		$this->getLogService()->info('knowledge', 'create', "添加知识点 {$knowledge['name']}(#{$knowledge['id']})", $knowledge);

		return $knowledge;
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

	public function isKnowledgeCodeAvaliable($code, $exclude = null)
	{
		if (empty($code)) {
		    return false;
		}

		if ($code == $exclude) {
		    return true;
		}

		$knowledge = $this->getKnowledgeDao()->findKnowledgeByCode($code);

		return $knowledge ? false : true;
	}

	public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId)
	{
		return $this->getKnowledgeDao()->findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);
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

	private function filterKnowledgeFields(&$knowledge, $releatedKnowledge = null)
    {
        foreach (array_keys($knowledge) as $key) {
            switch ($key) {
                case 'name':
                    $knowledge['name'] = (string) $knowledge['name'];
                    if (empty($knowledge['name'])) {
                        throw $this->createServiceException("名称不能为空，保存知识点失败");
                    }
                    break;
                case 'code':
                    if (empty($knowledge['code'])) {
                        throw $this->createServiceException("编码不能为空，保存知识点失败");
                    } else {
                        if (!preg_match("/^[a-zA-Z0-9_]+$/i", $knowledge['code'])) {
                            throw $this->createServiceException("编码({$knowledge['code']})含有非法字符，保存知识点失败");
                        }
                        if (ctype_digit($knowledge['code'])) {
                            throw $this->createServiceException("编码({$knowledge['code']})不能全为数字，保存知识点失败");
                        }
                        $exclude = empty($releatedKnowledge['code']) ? null : $releatedKnowledge['code'];
                        if (!$this->isknowledgeCodeAvaliable($knowledge['code'], $exclude)) {
                            throw $this->createServiceException("编码({$knowledge['code']})不可用，保存知识点失败");
                        }
                    }
                    break;
                case 'categoryId':
                    $knowledge['categoryId'] = (int) $knowledge['categoryId'];
                    $category = $this->getCategoryService()->getCategory($knowledge['categoryId']);
                    if (empty($category)) {
                        throw $this->createServiceException("知识点分组ID({$knowledge['categoryId']})不存在，保存知识点失败");
                    }
                    break;
                case 'parentId':
                    $knowledge['parentId'] = (int) $knowledge['parentId'];
                    if ($knowledge['parentId'] > 0) {
                        $parentknowledge = $this->getKnowledge($knowledge['parentId']);
                        if (empty($parentknowledge) or $parentknowledge['categoryId'] != $knowledge['categoryId']) {
                            throw $this->createServiceException("父知识点(ID:{$knowledge['categoryId']})不存在，保存知识点失败");
                        }
                    }
                    break;
            }
        }

        return $knowledge;
    }

	protected function getKnowledgeDao()
	{
		return $this->createDao('Taxonomy.KnowledgeDao');
	}

	protected function getCategoryService()
	{
		return $this->createService('Taxonomy.CategoryService');
	}

	private function getLogService()
	{
	    return $this->createService('System.LogService');
	}

}