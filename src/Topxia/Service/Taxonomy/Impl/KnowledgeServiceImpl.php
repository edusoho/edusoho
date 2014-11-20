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
       $knowledge = $this->checkExist($id);

        $fields = ArrayToolkit::parts($fields, array('description','name', 'weight', 'sequence', 'parentId'));
        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新知识点失败！');
        }

        // filterknowledgeFields里有个判断，需要用到这个$fields['groupId']

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
        $knowledge = ArrayToolkit::parts($knowledge, array('description','name', 'weight', 'subjectId', 'materialId', 'term', 'gradeId', 'parentId', 'isVisible', 'sequence'));

        if (!ArrayToolkit::requireds($knowledge, array('name', 'subjectId', 'materialId', 'term', 'gradeId', 'parentId'))) {
            throw $this->createServiceException("缺少必要参数，，添加知识点失败");
        }

        $this->filterKnowledgeFields($knowledge);
        $knowledge = $this->getKnowledgeDao()->createKnowledge($knowledge);

        $this->getLogService()->info('knowledge', 'create', "添加知识点 {$knowledge['name']}(#{$knowledge['id']})", $knowledge);

        return $knowledge;
    }

    public function searchKnowledges($conditions, $sort = 'sequence', $start, $limit)
    {
        return $this->getKnowledgeDao()->searchKnowledges($conditions, $sort, $start, $limit);
    }

    public function sort($id, $parentId, $seq)
    {
        $knowledge = $this->checkExist($id);
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

    public function findKnowledgeChildrenIds($id, &$result)
    {
        $knowledge = $this->getKnowledge($id);
        if(empty($knowledge)) {
            return $result;
        }
        $knowledges = $this->findKnowledgeByParentId($id);
        foreach ($knowledges as $key => $knowledge) {
            $result[] = $knowledge['id'];
            $this->findKnowledgeChildrenIds($knowledge['id'], $result); 
        }

        return $result;
    }

    public function findNodesData($parentId, $query)
    {
        unset($query['id']);
        if($parentId) {
            $knowledge = $this->checkExist($parentId);
            $query['parentId'] = $parentId;
            $knowledges = $this->searchKnowledges($query, array('sequence', 'ASC'), 0, 10000);
        } else {
            $query['parentId'] = 0;
            $knowledges = $this->searchKnowledges($query, array('sequence', 'ASC'), 0, 10000);
        }

        foreach ($knowledges as $key => $knowledge) {
            if(count($this->findKnowledgeByParentId($knowledge['id']))) {
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

    public function findKnowledgeByParentId($parentId)
    {
        return $this->getKnowledgeDao()->findKnowledgeByParentId($parentId);
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
                case 'parentId':
                    $knowledge['parentId'] = (int) $knowledge['parentId'];
                    if ($knowledge['parentId'] > 0) {
                        $parentknowledge = $this->getKnowledge($knowledge['parentId']);
                        if (empty($parentknowledge)) {
                            throw $this->createServiceException("父知识点(ID:{$knowledge['id']})不存在，保存知识点失败");
                        }
                    }
                    break;
            }
        }
        $knowledge['materialId'] = empty($knowledge['materialId']) ? 0 : $knowledge['materialId'];
        return $knowledge;
    }


    private function checkExist($knowledgeId)
    {
        $knowledge = $this->getKnowledge($knowledgeId);
        if (empty($knowledge)) {
            throw $this->createNoteFoundException("知识点(#{$knowledgeId})不存在，操作失败！");
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