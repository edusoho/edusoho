<?php

namespace Topxia\Service\ArticleMaterial\Dao;

interface ArticleMaterialDao
{
    public function getArticleMaterial($id);

    public function searchArticleMaterials($conditions, $orderBys, $start, $limit);

    public function searchArticleMaterialsCount($conditions);
    
    public function addArticleMaterial($articleMaterial);

    public function updateArticleMaterial($id,$articleMaterial);

    public function deleteArticleMaterial($id);
}