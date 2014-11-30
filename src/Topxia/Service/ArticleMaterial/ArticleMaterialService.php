<?php 
namespace Topxia\Service\ArticleMaterial;

interface ArticleMaterialService
{
    public function getArticleMaterial($id);

    public function searchArticleMaterials(array $conditions, $orderBy, $start, $limit);

    public function searchArticleMaterialsCount($conditions);

    public function createArticleMaterial($articleMaterial);

    public function updateArticleMaterial($id,$articleMaterial);

    public function deleteArticleMaterial($id);

    public function deleteArticleMaterialsByIds($ids);
}