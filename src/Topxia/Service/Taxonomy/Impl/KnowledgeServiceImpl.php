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

    public function updateKnowledge($id, $fields)
    {
       list($knowledge) = $this->checkExist(null, $id);

        $fields = ArrayToolkit::parts($fields, array('description','name', 'code', 'weight', 'sequence', 'parentId', 'isHidden'));
        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新知识点失败！');
        }

        // filterknowledgeFields里有个判断，需要用到这个$fields['groupId']
        $fields['categoryId'] = $knowledge['categoryId'];

        $this->filterKnowledgeFields($fields, $knowledge);

        $this->getLogService()->info('knowledge', 'update', "编辑知识点 {$knowledge['name']}(#{$id})", $fields);

        return $this->getKnowledgeDao()->updateKnowledge($id, $fields);
    }

    public function deleteKnowledge($id)
    {
        $ids = array();
        $ids = $this->findKnowledgeChildrenIds($id, $ids);
        $ids[] = $id;
        foreach ($ids as $id) {
            $this->getknowledgeDao()->deleteknowledge($id);
        }

        $this->getLogService()->info('knowledge', 'delete', "删除知识点(#{$id})");
    }

    public function createKnowledge($knowledge)
    {
        $knowledge = ArrayToolkit::parts($knowledge, array('description','name', 'code', 'weight', 'categoryId', 'parentId', 'isHidden', 'sequence'));

        if (!ArrayToolkit::requireds($knowledge, array('name', 'code', 'weight', 'categoryId', 'parentId'))) {
            throw $this->createServiceException("缺少必要参数，，添加知识点失败");
        }

        $this->filterKnowledgeFields($knowledge);
        $knowledge = $this->getKnowledgeDao()->createKnowledge($knowledge);

        $this->getLogService()->info('knowledge', 'create', "添加知识点 {$knowledge['name']}(#{$knowledge['id']})", $knowledge);

        return $knowledge;
    }

    public function sort($id, $parentId, $seq)
    {
        list($knowledge) = $this->checkExist(null, $id);
        if($knowledge['parentId'] != $parentId) {
            $this->updateKnowledge($id, array('parentId' => $parentId));
        } 
        $index = 1;
        foreach ($seq as $key => $knowledgeId) {
            $this->updateKnowledge($knowledgeId, array('sequence' => $index++));
        }
    }

    public function findKnowledgeByCategoryId($categoryId)
    {
        return $this->getKnowledgeDao()->findKnowledgeByCategoryId($categoryId);
    }
    
    public function findChildrenKnowledgeByCategoryId($categoryId)
    {
        return $this->getKnowledgeDao()->findChildrenKnowledgeByCategoryId($categoryId);
    }

    public function findParentKnowledgeByCategoryId($categoryId)
    {
        return $this->getKnowledgeDao()->findParentKnowledgeByCategoryId($categoryId);
    }

    public function findKnowledgeChildrenIds($id, &$result)
    {
        $knowledge = $this->getKnowledge($id);
        if(empty($knowledge)) {
            return $result;
        }
        $knowledges = $this->findKnowledgeByCategoryIdAndParentId($knowledge['categoryId'], $id);
        foreach ($knowledges as $key => $knowledge) {
            $result[] = $knowledge['id'];
            $this->findKnowledgeChildrenIds($knowledge['id'], $result); 
        }

        return $result;
    }

    public function findNodesData($categoryId, $parentId)
    {
        $this->checkExist($categoryId, $parentId);
        $knowledges = $this->findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);
        foreach ($knowledges as $key => $knowledge) {
            if(count($this->findKnowledgeByCategoryIdAndParentId($categoryId, $knowledge['id']))) {
                $knowledge['isParent'] = true;
            } else {
                $knowledge['isParent'] = false;
            }
            $knowledges[$key] = $knowledge;
        }
        return $knowledges;
    }

    public function findAllNodesData($categoryId, $parentId)
    {
        $this->checkExist($categoryId, $parentId);
        $knowledges = $this->findAllKnowledgeByCategoryIdAndParentId($categoryId, $parentId);
        foreach ($knowledges as $key => $knowledge) {
            if(count($this->findAllKnowledgeByCategoryIdAndParentId($categoryId, $knowledge['id']))) {
                $knowledge['isParent'] = true;
            } else {
                $knowledge['isParent'] = false;
            }
            $knowledges[$key] = $knowledge;
        }
        return $knowledges;
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

    public function findKnowledgeByIds(array $ids)
    {
        return $this->getKnowledgeDao()->findKnowledgeByIds($ids);
    }

    public function searchKnowledge($conditions,$orderBys,$start,$limit)
    {
        return $this->getKnowledgeDao()->searchKnowledge($conditions,$orderBys,$start,$limit);
    }

    public function findKnowledgeByCategoryIdAndParentId($categoryId, $parentId)
    {
        return $this->getKnowledgeDao()->findKnowledgeByCategoryIdAndParentId($categoryId, $parentId);
    }

    public function findAllKnowledgeByCategoryIdAndParentId($categoryId, $parentId)
    {
        return $this->getKnowledgeDao()->findAllKnowledgeByCategoryIdAndParentId($categoryId, $parentId);
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


    private function checkExist($categoryId, $knowledgeId)
    {
        $result = array();
        if($categoryId) {
            $category = $this->getCategoryService()->getCategory($categoryId);
            if (empty($category)) {
                throw $this->createServiceException("知识点Category #{$categoryId}，不存在");
            }
            $result[] = $category;
        }

        if($knowledgeId) {
            $knowledge = $this->getKnowledge($knowledgeId);
            if (empty($knowledge)) {
                throw $this->createNoteFoundException("知识点(#{$id})不存在，操作失败！");
            }
            $result[] = $knowledge;
        }

        return $result;
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