<?php 
namespace Topxia\Service\ArticleMaterial;

interface ArticleMaterialService
{
    public function getArticleMarerial($id);

    public function searchArticleMarerials(array $conditions, $orderBy, $start, $limit);

    public function searchArticleMarerialsCount($conditions);

    public function createArticleMarerial($articleMarerial);

    public function updateArticleMarerial($id,$articleMarerial);

    public function deleteArticleMarerial($id);

    public function deleteArticleMarerialsByIds($ids);
}