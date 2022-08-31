<?php


namespace ApiBundle\Api\Resource\LatestInformation;


use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Article\Service\ArticleService;

class LatestInformation extends AbstractResource
{
    public function search()
    {
        $information = $this ->getArticleService() -> searchArticles(['status' => 'published'], ['sticky' => 'DESC' ,'publishedTime' => 'DESC'], 0, 3);
        foreach ($information as &$info) {
            $info['body'] = AssetHelper::transformImages($info['body']);
            $info['thumb'] = AssetHelper::transformImagesAddUrl($info['thumb'], '');
            $info['originalThumb'] = AssetHelper::transformImagesAddUrl($info['originalThumb'], '');
            $info['picture'] = AssetHelper::transformImagesAddUrl($info['picture'], 'picture');
        }
        return $information;
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->service('Article:ArticleService');
    }
}