<?php 

namespace Topxia\Service\ArticleMaterial\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\ArticleMaterial\ArticleMaterialService;
use Topxia\Common\ArrayToolkit;

class ArticleMaterialServiceImpl extends BaseService implements ArticleMaterialService
{
    public function getArticleMaterial($id)
    {
        return $this->getArticleMaterialDao()->getArticleMaterial($id);
    }

    public function searchArticleMaterials(array $conditions, $orderBy, $start, $limit)
    {
        return $this->getArticleMaterialDao()->searchArticleMaterials($conditions,$orderBy,$start,$limit);
    }

    public function searchArticleMaterialsCount($conditions)
    {
        $count = $this->getArticleMaterialDao()->searchArticleMaterialsCount($conditions);
        return $count;
    }

    public function createArticleMaterial($articleMaterial)
    {
        if (empty($articleMaterial)) {
            $this->createServiceException("课件内容为空，创建课件失败！");
        }
        $articleMaterial = $this->filterArticleMaterialFields($articleMaterial);
        $articleMaterial = $this->getArticleMaterialDao()->addArticleMaterial($articleMaterial);

        $this->getLogService()->info('articleMaterial', 'create', "创建课件《({$articleMaterial['title']})》({$articleMaterial['id']})");
        
        return $articleMaterial;
    }

    public function updateArticleMaterial($id,$articleMaterial)
    {
        $articleMaterial = $this->filterArticleMaterialFields($articleMaterial);

        $this->getLogService()->info('articleMaterial', 'update', "更新课件《({$articleMaterial['title']})》({$id})");

        return $this->getArticleMaterialDao()->updateArticleMaterial($id,$articleMaterial);
    }

    public function deleteArticleMaterial($id)
    {
        $checkArticleMaterial = $this->getArticleMaterial($id);
        if(empty($checkArticleMaterial)){
            throw $this->createServiceException("课件不存在，操作失败。");
        }

        $this->getArticleMaterialDao()->deleteArticleMaterial($id);
        $this->getLogService()->info('ArticleMaterial', 'delete', "课件#{$id}永久删除");
        return true;
    }

    public function deleteArticleMaterialsByIds($ids)
    {
        if(count($ids) == 1){
            $this->deleteArticleMaterial($ids[0]);
        }else{
            foreach ($ids as $id) {
                $this->deleteArticleMaterial($id);
            }
        }
        return true;
    }

    private function getArticleMaterialDao()
    {
        return $this->createDao('ArticleMaterial.ArticleMaterialDao');
    }

    private function filterArticleMaterialFields($articleMaterial)
    {
        $articleMaterial = ArrayToolkit::parts($articleMaterial,array('content','knowledgeIds','mainKnowledgeId','relatedKnowledgeIds','tagIds','title','categoryId'));
        $articleMaterial['userId'] = $this->getCurrentUser()->id;
        $articleMaterial['createdTime'] = time();

        $articleMaterial['knowledgeIds'] = $articleMaterial['relatedKnowledgeIds'].",".$articleMaterial['mainKnowledgeId'];
        if (empty($articleMaterial['relatedKnowledgeIds'])) {
            $articleMaterial['knowledgeIds'] = $articleMaterial['mainKnowledgeId'];
        }

        $articleMaterial['knowledgeIds'] = array_filter(explode(',', $articleMaterial['knowledgeIds']));
        $articleMaterial['relatedKnowledgeIds'] = array_filter(explode(',', $articleMaterial['relatedKnowledgeIds']));
        return $articleMaterial;
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }
}