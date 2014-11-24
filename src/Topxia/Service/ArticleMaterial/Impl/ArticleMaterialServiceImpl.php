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
        if (empty($ArticleMaterial)) {
            $this->createServiceException("课件内容为空，创建课件失败！");
        }

        $ArticleMaterial = $this->filterArticleMaterialFields($ArticleMaterial);
        $ArticleMaterial = $this->getArticleMaterialDao()->addArticleMaterial($ArticleMaterial);

        $this->getLogService()->info('ArticleMaterial', 'create', "创建课件《({$ArticleMaterial['title']})》({$ArticleMaterial['id']})");
        
        return $ArticleMaterial;
    }

    public function updateArticleMaterial($id,$articleMaterial)
    {
        $ArticleMaterial = $this->filterArticleMaterialFields($ArticleMaterial);

        $this->getLogService()->info('ArticleMaterial', 'update', "更新课件《({$ArticleMaterial['title']})》({$id})");

        return $this->getArticleMaterialDao()->updateArticleMaterial($id,$ArticleMaterial);
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
        $ArticleMaterial = ArrayToolkit::parts($articleMaterial,array('knowledgeIds','mainKnowledgeId','relatedKnowledgeIds','tagIds','source','title','image','categoryId','url'));
        $ArticleMaterial['type'] = 'video';
        $ArticleMaterial['userId'] = $this->getCurrentUser()->id;
        $ArticleMaterial['createdTime'] = time();
        return $ArticleMaterial;
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }
}