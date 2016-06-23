<?php
namespace Topxia\Service\Article\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Article\Dao\ArticleDao;

class ArticleDaoImpl extends BaseDao implements ArticleDao
{
    protected $table = 'article';

    private $serializeFields = array(
        'tagIds' => 'saw'
    );

    public function getArticle($id)
    {
        $sql     = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $article = $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;

        return $article ? $this->createSerializer()->unserialize($article, $this->serializeFields) : null;
    }

    public function getArticlePrevious($categoryId, $createdTime)
    {
        $sql     = "SELECT * FROM {$this->table} WHERE `categoryId` = ? AND createdTime < ? ORDER BY `createdTime` DESC LIMIT 1";
        $article = $this->getConnection()->fetchAssoc($sql, array($categoryId, $createdTime) ?: null);

        return $article ? $this->createSerializer()->unserialize($article, $this->serializeFields) : null;
    }

    public function getArticleNext($categoryId, $createdTime)
    {
        $sql     = "SELECT * FROM {$this->table} WHERE  `categoryId` = ? AND createdTime > ? ORDER BY `createdTime` ASC LIMIT 1";
        $article = $this->getConnection()->fetchAssoc($sql, array($categoryId, $createdTime) ?: null);

        return $article ? $this->createSerializer()->unserialize($article, $this->serializeFields) : null;
    }

    public function getArticleByAlias($alias)
    {
        $sql     = "SELECT * FROM {$this->table} WHERE alias = ? LIMIT 1";
        $article = $this->getConnection()->fetchAssoc($sql, array($alias));

        return $article ? $this->createSerializer()->unserialize($article, $this->serializeFields) : null;
    }

    public function findArticlesByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }

        $marks    = str_repeat('?,', count($ids) - 1) . '?';
        $sql      = "SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        $articles = $this->getConnection()->fetchAll($sql, $ids);

        return $articles ? $this->createSerializer()->unserializes($articles, $this->serializeFields) : array();
    }

    public function findAllArticles()
    {
        $sql      = "SELECT * FROM {$this->table};";
        $articles = $this->getConnection()->fetchAll($sql, array());
        return $articles ? $this->createSerializer()->unserializes($articles, $this->serializeFields) : array();
    }

    public function findArticlesByCategoryIds(array $categoryIds, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        if (empty($categoryIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($categoryIds) - 1) . '?';
        $sql   = "SELECT * FROM {$this->table} WHERE categoryId in ({$marks}) ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        $artilces = $this->getConnection()->fetchAll($sql, $categoryIds);

        return $this->createSerializer()->unserializes($artilces, $this->serializeFields);
    }

    public function findArticlesCount(array $categoryIds)
    {
        if (empty($categoryIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($categoryIds) - 1) . '?';
        $sql   = "SELECT COUNT(id) FROM {$this->table} WHERE categoryId in ({$marks})";

        return $this->getConnection()->fetchColumn($sql, $categoryIds);
    }

    //@todo:sql
    public function searchArticles($conditions, $orderBys, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit);

        foreach ($orderBys as $orderBy) {
            $builder->addOrderBy($orderBy[0], $orderBy[1]);
        }

        $articles = $builder->execute()->fetchAll() ?: array();

        return $this->createSerializer()->unserializes($articles, $this->serializeFields);
    }

    public function searchArticlesCount($conditions)
    {
        $builder = $this->_createSearchQueryBuilder($conditions)
            ->select('COUNT(id)');

        return $builder->execute()->fetchColumn(0);
    }

    public function addArticle($article)
    {
        $article['createdTime'] = time();
        $article['updatedTime'] = $article['createdTime'];
        $article                = $this->createSerializer()->serialize($article, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $article);

        if ($affected <= 0) {
            throw $this->createDaoException('Insert Article error.');
        }

        return $this->getArticle($this->getConnection()->lastInsertId());
    }

    public function waveArticle($id, $field, $diff)
    {
        $fields = array('hits', 'upsNum', 'postNum');

        if (!in_array($field, $fields)) {
            throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
        }

        $currentTime = time();
        $sql         = "UPDATE {$this->table} SET {$field} = {$field} + ?, updatedTime = {$currentTime} WHERE id = ? LIMIT 1";

        return $this->getConnection()->executeQuery($sql, array($diff, $id));
    }

    public function updateArticle($id, $article)
    {
        $article                = $this->createSerializer()->serialize($article, $this->serializeFields);
        $article['updatedTime'] = time();
        $this->getConnection()->update($this->table, $article, array('id' => $id));

        return $this->getArticle($id);
    }

    public function deleteArticle($id)
    {
        $this->getConnection()->delete('thread_post', array('targetId' => $id, 'targetType' => 'article'));
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function findPublishedArticlesByTagIdsAndCount($tagIds, $count)
    {
        $sql      = "SELECT * FROM {$this->table} WHERE status = 'published'";
        $length   = count($tagIds);
        $tagArray = array();
        $sql .= " AND (";

        for ($i = 0; $i < $length; $i++) {
            $sql .= "  tagIds LIKE  ? ";

            if ($i != $length - 1) {
                $sql .= " OR ";
            }

            $tagArray[] = '%|' . $tagIds[$i] . '|%';
        }

        $sql .= " ) ";

        $sql .= " ORDER BY publishedTime DESC LIMIT 0, {$count}";

        return $this->getConnection()->fetchAll($sql, $tagArray);
    }

    protected function _createSearchQueryBuilder($conditions)
    {
        $conditions = array_filter($conditions);

        if (array_key_exists('property', $conditions)) {
            $key              = $conditions['property'];
            $conditions[$key] = 1;
        }

        if (array_key_exists('hasPicture', $conditions)) {
            if ($conditions['hasPicture']) {
                $conditions['pictureNull'] = "";
                unset($conditions['hasPicture']);
            }
        }

        if (array_key_exists('hasThumb', $conditions)) {
            $conditions['thumbNotEqual'] = '';
            unset($conditions['hasThumb']);
        }

        if (isset($conditions['keywords'])) {
            $conditions['keywords'] = "%{$conditions['keywords']}%";
        }

        if (isset($conditions['tagId'])) {
            $conditions['tagId'] = "%|{$conditions['tagId']}|%";
        }

        if(isset($conditions['likeOrgCode'])){
            $conditions['likeOrgCode'] = $conditions['likeOrgCode'] . '%';
            unset($conditions['orgCode']);
        }

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, 'article')
            ->andWhere('status = :status')
            ->andWhere('categoryId = :categoryId')
            ->andWhere('featured = :featured')
            ->andWhere('promoted = :promoted')
            ->andWhere('sticky = :sticky')
            ->andWhere('title LIKE :keywords')
            ->andWhere('picture != :pictureNull')
            ->andWhere('tagIds LIKE :tagId')
            ->andWhere('updatedTime >= :updatedTime_GE')
            ->andWhere('categoryId = :categoryId')
            ->andWhere('categoryId IN (:categoryIds)')
            ->andWhere('orgCode LIKE :likeOrgCode')
            ->andWhere('id != :idNotEqual')
            ->andWhere('thumb != :thumbNotEqual')
            ->andWhere('orgCode = :orgCode');

        return $builder;
    }
}
