<?php
namespace Topxia\Service\Article\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Article\ArticleService;

class ArticleServiceImpl extends BaseService implements ArticleService
{
    public function getArticle($id)
    {
        return $this->getArticleDao()->getArticle($id);
    }

    public function getArticlePrevious($currentArticleId)
    {
        $article = $this->getArticle($currentArticleId);

        if (empty($article)) {
            $this->createServiceException('文章内容为空,操作失败！');
        }

        $createdTime = $article['createdTime'];
        $categoryId  = $article['categoryId'];
        $category    = $this->getCategoryService()->getCategory($categoryId);

        if (empty($category)) {
            $this->createServiceException('文章分类不存在,操作失败！');
        }

        return $this->getArticleDao()->getArticlePrevious($categoryId, $createdTime);
    }

    public function getArticleNext($currentArticleId)
    {
        $article = $this->getArticle($currentArticleId);

        if (empty($article)) {
            $this->createServiceException('文章内容为空,操作失败！');
        }

        $createdTime = $article['createdTime'];
        $categoryId  = $article['categoryId'];
        $category    = $this->getCategoryService()->getCategory($categoryId);

        if (empty($category)) {
            $this->createServiceException('文章分类不存在,操作失败！');
        }

        return $this->getArticleDao()->getArticleNext($categoryId, $createdTime);
    }

    public function getArticleByAlias($alias)
    {
        return $this->getArticleDao()->getArticleByAlias($alias);
    }

    public function findAllArticles()
    {
        return $this->getArticleDao()->findAllArticles();
    }

    public function findArticlesByCategoryIds(array $categoryIds, $start, $limit)
    {
        return $this->getArticleDao()->findArticlesByCategoryIds($categoryIds, $start, $limit);
    }

    public function findArticlesByIds($ids)
    {
        return ArrayToolkit::index($this->getArticleDao()->findArticlesByIds($ids), 'id');
    }

    public function findArticlesCount(array $categoryIds)
    {
        return $this->getArticleDao()->findArticlesCount($categoryIds);
    }

    public function searchArticles(array $conditions, $sort, $start, $limit)
    {
        $orderBys = $this->filterSort($sort);

        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getArticleDao()->searchArticles($conditions, $orderBys, $start, $limit);
    }

    public function searchArticlesCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getArticleDao()->searchArticlesCount($conditions);
    }

    public function createArticle($article)
    {
        if (empty($article)) {
            throw $this->createServiceException("文章内容为空，创建文章失败！");
        }

        $article = $this->filterArticleFields($article, 'add');
        $article = $this->getArticleDao()->addArticle($article);

        $this->getLogService()->info('article', 'create', "创建文章《({$article['title']})》({$article['id']})");

        $this->dispatchEvent('article.create', $article);

        return $article;
    }

    public function updateArticle($id, $article)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            throw $this->createServiceException("文章不存在，操作失败。");
        }

        $article = $this->filterArticleFields($article);

        $article = $this->getArticleDao()->updateArticle($id, $article);

        $this->getLogService()->info('article', 'update', "修改文章《({$article['title']})》({$article['id']})");
        $this->dispatchEvent('article.update', new ServiceEvent($article));

        return $article;
    }

    public function batchUpdateOrg($articleIds, $orgCode)
    {
        if (!is_array($articleIds)) {
            $articleIds = array($articleIds);
        }
        $fields = $this->fillOrgId(array('orgCode' => $orgCode));
        foreach ($articleIds as $articleId) {
            $user = $this->getArticleDao()->updateArticle($articleId, $fields);
        }
    }

    public function hitArticle($id)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            throw $this->createServiceException("文章不存在，操作失败。");
        }

        $this->getArticleDao()->waveArticle($id, 'hits', +1);
    }

    public function getArticleLike($articleId, $userId)
    {
        return $this->getArticleLikeDao()->getArticleLikeByArticleIdAndUserId($articleId, $userId);
    }

    public function like($articleId)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            throw $this->createNotFoundException("用户还未登录,不能点赞。");
        }

        $article = $this->getArticle($articleId);

        if (empty($article)) {
            throw $this->createNotFoundException("资讯不存在，或已删除。");
        }

        $like = $this->getArticleLike($articleId, $user['id']);

        if (!empty($like)) {
            throw $this->createAccessDeniedException('不可重复对一条资讯点赞！');
        }

        $articleLike = array(
            'articleId'   => $articleId,
            'userId'      => $user['id'],
            'createdTime' => time()
        );

        $this->getDispatcher()->dispatch('article.liked', new ServiceEvent($article));

        return $this->getArticleLikeDao()->addArticleLike($articleLike);
    }

    public function cancelLike($articleId)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            throw $this->createNotFoundException("用户还未登录,不能点赞。");
        }

        $article = $this->getArticle($articleId);

        if (empty($article)) {
            throw $this->createNotFoundException("资讯不存在，或已删除。");
        }

        $this->getArticleLikeDao()->deleteArticleLikeByArticleIdAndUserId($articleId, $user['id']);

        $this->getDispatcher()->dispatch('article.cancelLike', new ServiceEvent($article));
    }

    public function count($articleId, $field, $diff)
    {
        $this->getArticleDao()->waveArticle($articleId, $field, $diff);
    }

    public function setArticleProperty($id, $property)
    {
        $article = $this->getArticleDao()->getArticle($id);

        if (empty($property)) {
            throw $this->createServiceException('属性{$property}不存在，更新失败！');
        }

        $propertyVal = 1;
        $this->getArticleDao()->updateArticle($id, array("{$property}" => $propertyVal));

        $this->getLogService()->info('article', 'update_property', "文章#{$id},$article[$property]=>{$propertyVal}");
        return $propertyVal;
    }

    public function cancelArticleProperty($id, $property)
    {
        $article = $this->getArticleDao()->getArticle($id);

        if (empty($property)) {
            throw $this->createServiceException('属性{$property}不存在，更新失败！');
        }

        $propertyVal = 0;
        $this->getArticleDao()->updateArticle($id, array("{$property}" => $propertyVal));

        $this->getLogService()->info('article', 'cancel_property', "文章#{$id},$article[$property]=>{$propertyVal}");

        return $propertyVal;
    }

    public function trashArticle($id)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            throw $this->createServiceException("文章不存在，操作失败。");
        }

        $this->getArticleDao()->updateArticle($id, $fields = array('status' => 'trash'));
        $this->dispatchEvent('article.trash', new ServiceEvent($checkArticle));
        $this->getLogService()->info('article', 'trash', "文章#{$id}移动到回收站");
    }

    public function removeArticlethumb($id)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            throw $this->createServiceException("文章不存在，操作失败。");
        }

        $this->getArticleDao()->updateArticle($id, $fields = array('thumb' => '', 'originalThumb' => ''));
        $this->getFileService()->deleteFileByUri($checkArticle["thumb"]);
        $this->getFileService()->deleteFileByUri($checkArticle["originalThumb"]);
        $this->getLogService()->info('article', 'removeThumb', "文章#{$id}removeThumb");
    }

    public function deleteArticle($id)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            throw $this->createServiceException("文章不存在，操作失败。");
        }

        $res = $this->getArticleDao()->deleteArticle($id);
        $this->dispatchEvent('article.delete', new ServiceEvent($checkArticle));
        $this->getLogService()->info('article', 'delete', "文章#{$id}永久删除");

        return true;
    }

    public function deleteArticlesByIds($ids)
    {
        if (count($ids) == 1) {
            $this->deleteArticle($ids[0]);
        } else {
            foreach ($ids as $id) {
                $this->deleteArticle($id);
            }
        }
    }

    public function publishArticle($id)
    {
        $article = $this->getArticleDao()->updateArticle($id, $fields = array('status' => 'published'));
        $this->getLogService()->info('article', 'publish', "文章#{$id}发布");
        $this->dispatchEvent('article.publish', $article);
    }

    public function unpublishArticle($id)
    {
        $article = $this->getArticleDao()->updateArticle($id, $fields = array('status' => 'unpublished'));
        $this->getLogService()->info('article', 'unpublish', "文章#{$id}未发布");
        $this->dispatchEvent('article.unpublish', $article);
    }

    public function changeIndexPicture($data)
    {
        $fileIds = ArrayToolkit::column($data, "id");
        $files   = $this->getFileService()->getFilesByIds($fileIds);

        $data  = ArrayToolkit::index($data, "type");
        $files = ArrayToolkit::index($files, "id");

        foreach ($data as $key => $value) {
            if ($key == "origin") {
                $file               = $this->getFileService()->getFileObject($value["id"]);
                $file               = $this->getFileService()->uploadFile("article", $file);
                $data[$key]["file"] = $file;

                $this->getFileService()->deleteFileByUri($files[$value["id"]]["uri"]);
            } else {
                $data[$key]["file"] = $files[$value["id"]];
            }
        }

        return $data;
    }

    public function findPublishedArticlesByTagIdsAndCount($tagIds, $count)
    {
        return $this->getArticleDao()->findPublishedArticlesByTagIdsAndCount($tagIds, $count);
    }

    public function viewArticle($id)
    {
        $article = $this->getArticle($id);

        if (empty($article)) {
            return;
        }

        $user = $this->getCurrentUser();
        $this->dispatchEvent('article.view', new ServiceEvent($article));
        $this->hitArticle($id);

        return $article;
    }

    public function findRelativeArticles($articleId, $num = 3)
    {
        $article = $this->getArticle($articleId);

        if (empty($article)) {
            throw $this->createNotFoundException(sprintf('article #%s not found', $articleId));
        }

        $self = $this;

        $relativeArticles = array_map(function ($tagId) use ($article, $self) {
            $conditions = array(
                'tagId'      => $tagId,
                'idNotEqual' => $article['id'],
                'hasThumb'   => true
            );
            $count    = $self->searchArticlesCount($conditions);
            $articles = $self->searchArticles($conditions, 'normal', 0, $count);
            return ArrayToolkit::index($articles, 'id');
        }, $article['tagIds']);

        $ret = array_reduce($relativeArticles, function ($ret, $articles) {
            return array_merge($ret, $articles);
        }, array());
        $ret = array_unique($ret, SORT_REGULAR);
        return array_slice($ret, 0, $num);
    }

    protected function filterArticleFields($fields, $mode = 'update')
    {
        $article            = array();
        $user               = $this->getCurrentUser();
        $match              = preg_match('/<\s*img.+?src\s*=\s*[\"|\'](.*?)[\"|\']/i', $fields['body'], $matches);
        $article['picture'] = $match ? $matches[1] : "";

        $article['thumb']         = $fields['thumb'];
        $article['originalThumb'] = $fields['originalThumb'];
        $article['title']         = $fields['title'];
        $article['body']          = $fields['body'];
        $article['featured']      = empty($fields['featured']) ? 0 : 1;
        $article['promoted']      = empty($fields['promoted']) ? 0 : 1;
        $article['sticky']        = empty($fields['sticky']) ? 0 : 1;

        $article['categoryId']    = $fields['categoryId'];
        $article['source']        = $fields['source'];
        $article['sourceUrl']     = $fields['sourceUrl'];
        $article['publishedTime'] = strtotime($fields['publishedTime']);
        $article['updatedTime']   = time();

        $fields = $this->fillOrgId($fields);
        if (isset($fields['orgId'])) {
            $article['orgCode'] = $fields['orgCode'];
            $article['orgId']   = $fields['orgId'];
        }
        if (!empty($fields['tags']) && !is_array($fields['tags'])) {
            $fields['tags']    = explode(",", $fields['tags']);
            $article['tagIds'] = ArrayToolkit::column($this->getTagService()->findTagsByNames($fields['tags']), 'id');
        } else {
            $article['tagIds'] = array();
        }

        if ($mode == 'add') {
            $article['status']      = 'published';
            $article['userId']      = $user->id;
            $article['createdTime'] = time();
        }

        return $article;
    }

    protected function prepareSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (!empty($conditions['includeChildren']) && isset($conditions['categoryId'])) {
            $childrenIds               = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            unset($conditions['categoryId']);
            unset($conditions['includeChildren']);
        }

        return $conditions;
    }

    protected function filterSort($sort)
    {
        if (is_array($sort)) {
            return array($sort);
        }

        switch ($sort) {
            case 'created':
                $orderBys = array(
                    array('createdTime', 'DESC')
                );
                break;

            case 'published':
                $orderBys = array(
                    array('sticky', 'DESC'),
                    array('publishedTime', 'DESC')
                );
                break;

            case 'normal':
                $orderBys = array(
                    array('publishedTime', 'DESC')
                );
                break;

            case 'popular':
                $orderBys = array(
                    array('hits', 'DESC')
                );
                break;

            default:
                throw $this->createServiceException('参数sort不正确。');
        }

        return $orderBys;
    }

    protected function getArticleDao()
    {
        return $this->createDao('Article.ArticleDao');
    }

    protected function getArticleLikeDao()
    {
        return $this->createDao('Article.ArticleLikeDao');
    }

    protected function getCategoryService()
    {
        return $this->createService('Article.CategoryService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

    protected function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }
}
