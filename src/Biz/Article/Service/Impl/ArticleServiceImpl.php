<?php

namespace Biz\Article\Service\Impl;

use AppBundle\Common\SimpleValidator;
use Biz\Article\ArticleException;
use Biz\Article\CategoryException;
use Biz\BaseService;
use Biz\Article\Dao\ArticleDao;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\System\Service\LogService;
use Biz\Article\Dao\ArticleLikeDao;
use Biz\Taxonomy\Service\TagService;
use Biz\Article\Service\ArticleService;
use Biz\User\UserException;
use Codeages\Biz\Framework\Event\Event;
use Biz\Article\Service\CategoryService;

class ArticleServiceImpl extends BaseService implements ArticleService
{
    public function getArticle($id)
    {
        return $this->getArticleDao()->get($id);
    }

    public function getArticlePrevious($currentArticleId)
    {
        $article = $this->getArticle($currentArticleId);

        if (empty($article)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $createdTime = $article['createdTime'];
        $categoryId = $article['categoryId'];
        $category = $this->getCategoryService()->getCategory($categoryId);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        return $this->getArticleDao()->getPrevious($categoryId, $createdTime);
    }

    public function getArticleNext($currentArticleId)
    {
        $article = $this->getArticle($currentArticleId);

        if (empty($article)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $createdTime = $article['createdTime'];
        $categoryId = $article['categoryId'];
        $category = $this->getCategoryService()->getCategory($categoryId);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        return $this->getArticleDao()->getNext($categoryId, $createdTime);
    }

    public function getArticleByAlias($alias)
    {
        return $this->getArticleDao()->getByAlias($alias);
    }

    public function findAllArticles()
    {
        return $this->getArticleDao()->findAll();
    }

    public function findArticlesByCategoryIds(array $categoryIds, $start, $limit)
    {
        return $this->getArticleDao()->searchByCategoryIds($categoryIds, $start, $limit);
    }

    public function findArticlesByIds($ids)
    {
        return ArrayToolkit::index($this->getArticleDao()->findByIds($ids), 'id');
    }

    public function findArticlesCount(array $categoryIds)
    {
        return $this->getArticleDao()->countByCategoryIds($categoryIds);
    }

    public function searchArticles(array $conditions, $sort, $start, $limit)
    {
        $orderBys = $this->filterSort($sort);

        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getArticleDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countArticles($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getArticleDao()->count($conditions);
    }

    public function createArticle($article)
    {
        $user = $this->getCurrentUser();

        if (empty($article)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $article = $this->filterArticleFields($article, 'add');

        $tagIds = $article['tagIds'];

        unset($article['tagIds']);

        $article = $this->getArticleDao()->create($article);

        $this->dispatchEvent('article.create', new Event($article, array('tagIds' => $tagIds, 'userId' => $user['id'])));

        return $article;
    }

    public function updateArticle($id, $article)
    {
        $user = $this->getCurrentUser();

        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $article = $this->filterArticleFields($article);

        if (!empty($article['tagIds'])) {
            $tagIds = $article['tagIds'];

            unset($article['tagIds']);
        } else {
            $tagIds = array();
            unset($article['tagIds']);
        }

        $article = $this->getArticleDao()->update($id, $article);

        $event = new Event($article, array(
            'tagIds' => $tagIds,
            'userId' => $user['id'],
        ));
        $this->dispatchEvent('article.update', $event);

        return $article;
    }

    public function batchUpdateOrg($articleIds, $orgCode)
    {
        if (!is_array($articleIds)) {
            $articleIds = array($articleIds);
        }
        $fields = $this->fillOrgId(array('orgCode' => $orgCode));
        foreach ($articleIds as $articleId) {
            $this->getArticleDao()->update($articleId, $fields);
        }
    }

    public function hitArticle($id)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $this->getArticleDao()->waveArticle($id, 'hits', +1);
    }

    public function getArticleLike($articleId, $userId)
    {
        return $this->getArticleLikeDao()->getByArticleIdAndUserId($articleId, $userId);
    }

    public function like($articleId)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $article = $this->getArticle($articleId);

        if (empty($article)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $like = $this->getArticleLike($articleId, $user['id']);

        if (!empty($like)) {
            $this->createNewException(ArticleException::DUPLICATE_LIKE());
        }

        $articleLike = array(
            'articleId' => $articleId,
            'userId' => $user['id'],
            'createdTime' => time(),
        );

        $this->dispatchEvent('article.liked', $article);

        return $this->getArticleLikeDao()->create($articleLike);
    }

    public function cancelLike($articleId)
    {
        $user = $this->getCurrentUser();

        if (empty($user)) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $article = $this->getArticle($articleId);

        if (empty($article)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $this->getArticleLikeDao()->deleteByArticleIdAndUserId($articleId, $user['id']);

        $this->dispatchEvent('article.cancelLike', $article);
    }

    public function count($articleId, $field, $diff)
    {
        $this->getArticleDao()->waveArticle($articleId, $field, $diff);
    }

    public function setArticleProperty($id, $property)
    {
        $article = $this->getArticleDao()->get($id);

        if (empty($property)) {
            $this->createNewException(ArticleException::PROPERTY_INVALID());
        }

        $propertyVal = 1;
        $this->getArticleDao()->update($id, array("{$property}" => $propertyVal));

        return $propertyVal;
    }

    public function cancelArticleProperty($id, $property)
    {
        $article = $this->getArticleDao()->get($id);

        if (empty($property)) {
            $this->createNewException(ArticleException::PROPERTY_INVALID());
        }

        $propertyVal = 0;
        $this->getArticleDao()->update($id, array("{$property}" => $propertyVal));

        return $propertyVal;
    }

    public function trashArticle($id)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $this->getArticleDao()->update($id, $fields = array('status' => 'trash'));
        $this->dispatchEvent('article.trash', $checkArticle);
    }

    public function removeArticlethumb($id)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $this->getArticleDao()->update($id, $fields = array('thumb' => '', 'originalThumb' => ''));
        $this->getFileService()->deleteFileByUri($checkArticle['thumb']);
        $this->getFileService()->deleteFileByUri($checkArticle['originalThumb']);
    }

    public function deleteArticle($id)
    {
        $checkArticle = $this->getArticle($id);

        if (empty($checkArticle)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $this->getArticleDao()->delete($id);
        $this->dispatchEvent('article.delete', $checkArticle);
        $this->getLogService()->info('article', 'delete', "文章#{$id}永久删除");

        return true;
    }

    public function deleteArticlesByIds(array $ids)
    {
        foreach ($ids as $id) {
            $this->deleteArticle($id);
        }
    }

    public function publishArticle($id)
    {
        $article = $this->getArticleDao()->update($id, $fields = array('status' => 'published'));
        $this->dispatchEvent('article.publish', $article);
    }

    public function unpublishArticle($id)
    {
        $article = $this->getArticleDao()->update($id, $fields = array('status' => 'unpublished'));
        $this->dispatchEvent('article.unpublish', $article);
    }

    public function changeIndexPicture($data)
    {
        $fileIds = ArrayToolkit::column($data, 'id');
        $files = $this->getFileService()->getFilesByIds($fileIds);

        $data = ArrayToolkit::index($data, 'type');
        $files = ArrayToolkit::index($files, 'id');

        foreach ($data as $key => $value) {
            if ('origin' == $key) {
                $file = $this->getFileService()->getFileObject($value['id']);
                $file = $this->getFileService()->uploadFile('article', $file);
                $data[$key]['file'] = $file;

                $this->getFileService()->deleteFileByUri($files[$value['id']]['uri']);
            } else {
                $data[$key]['file'] = $files[$value['id']];
            }
        }

        return $data;
    }

    public function findPublishedArticlesByTagIdsAndCount($tagIds, $count)
    {
        $articles = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, 'article');

        return $this->getArticleDao()->search(array('articleIds' => ArrayToolkit::column($articles, 'id'), 'status' => 'published'), array('publishedTime' => 'DESC'), 0, $count);
    }

    public function viewArticle($id)
    {
        $article = $this->getArticle($id);

        if (empty($article)) {
            return array();
        }

        $this->dispatchEvent('article.view', $article);
        $this->hitArticle($id);

        return $article;
    }

    public function findRelativeArticles($articleId, $num = 3)
    {
        $article = $this->getArticle($articleId);

        if (empty($article)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $tags = $this->getTagService()->findTagsByOwner(array('ownerType' => 'article', 'ownerId' => $articleId));

        $tagIds = ArrayToolkit::column($tags, 'id');

        $tagOwnerRelations = $this->getTagService()->findTagOwnerRelationsByTagIdsAndOwnerType($tagIds, 'article');
        $articleIds = ArrayToolkit::column($tagOwnerRelations, 'ownerId');

        foreach ($articleIds as $key => $articleId) {
            if ($articleId == $article['id']) {
                unset($articleIds[$key]);
            }
        }

        $self = $this;
        $relativeArticles = array_map(function ($articleId) use ($article, $self) {
            $conditions = array(
                'articleId' => $articleId,
                'hasThumb' => true,
                'status' => 'published',
            );
            $articles = $self->searchArticles($conditions, 'normal', 0, PHP_INT_MAX);

            return ArrayToolkit::index($articles, 'id');
        }, $articleIds);

        $ret = array_reduce($relativeArticles, function ($ret, $articles) {
            return array_merge($ret, $articles);
        }, array());
        $ret = array_unique($ret, SORT_REGULAR);

        return array_slice($ret, 0, $num);
    }

    protected function filterArticleFields($fields, $mode = 'update')
    {
        $article = array();
        $user = $this->getCurrentUser();
        $match = preg_match('/<\s*img.+?src\s*=\s*[\"|\'](.*?)[\"|\']/i', $fields['body'], $matches);
        $article['picture'] = $match ? $matches[1] : '';

        $article['thumb'] = $fields['thumb'];
        $article['originalThumb'] = $fields['originalThumb'];
        $article['title'] = $this->purifyHtml($fields['title']);
        $article['body'] = $this->purifyHtml($fields['body'], true);
        $article['featured'] = empty($fields['featured']) ? 0 : 1;
        $article['promoted'] = empty($fields['promoted']) ? 0 : 1;
        $article['sticky'] = empty($fields['sticky']) ? 0 : 1;

        $article['categoryId'] = $fields['categoryId'];
        $article['source'] = $this->purifyHtml($fields['source']);
        $article['sourceUrl'] = empty($fields['sourceUrl']) ? '' : $fields['sourceUrl'];
        $article['publishedTime'] = strtotime($fields['publishedTime']);

        if (!empty($article['sourceUrl']) && !SimpleValidator::site($article['sourceUrl'])) {
            $this->createNewException(ArticleException::SOURCE_URL_INVALID());
        }

        $fields = $this->fillOrgId($fields);
        if (isset($fields['orgId'])) {
            $article['orgCode'] = $fields['orgCode'];
            $article['orgId'] = $fields['orgId'];
        }
        if (!empty($fields['tags']) && !is_array($fields['tags'])) {
            $fields['tags'] = explode(',', $fields['tags']);
            $article['tagIds'] = ArrayToolkit::column($this->getTagService()->findTagsByNames($fields['tags']), 'id');
        } else {
            $article['tagIds'] = array();
        }

        if ('add' == $mode) {
            $article['status'] = 'published';
            $article['userId'] = $user->id;
        }

        return $article;
    }

    protected function prepareSearchConditions($conditions)
    {
        $conditions = array_filter($conditions);

        if (!empty($conditions['includeChildren']) && isset($conditions['categoryId'])) {
            $childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
            $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            unset($conditions['categoryId']);
            unset($conditions['includeChildren']);
        }

        return $conditions;
    }

    protected function filterSort($sort)
    {
        if (is_array($sort)) {
            return $sort;
        }

        switch ($sort) {
            case 'created':
                $orderBys = array(
                    'createdTime' => 'DESC',
                );
                break;
            case 'updated':
                $orderBys = array(
                    'updatedTime' => 'DESC',
                );
                break;

            case 'published':
                $orderBys = array(
                    'sticky' => 'DESC',
                    'publishedTime' => 'DESC',
                );
                break;

            case 'normal':
                $orderBys = array(
                    'publishedTime' => 'DESC',
                );
                break;

            case 'popular':
                $orderBys = array(
                    'hits' => 'DESC',
                );
                break;

            default:
                $this->createNewException(ArticleException::SORT_INVALID());
        }

        return $orderBys;
    }

    /**
     * @return ArticleDao
     */
    protected function getArticleDao()
    {
        return $this->createDao('Article:ArticleDao');
    }

    /**
     * @return ArticleLikeDao
     */
    protected function getArticleLikeDao()
    {
        return $this->createDao('Article:ArticleLikeDao');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Article:CategoryService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }
}
