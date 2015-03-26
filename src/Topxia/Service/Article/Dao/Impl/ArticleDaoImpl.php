<?php
namespace Topxia\Service\Article\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Article\Dao\ArticleDao;

class ArticleDaoImpl extends BaseDao implements ArticleDao
{
	protected $table = 'article';

    private $serializeFields = array(
            'tagIds' => 'json',
    );


	public function getArticle($id)
	{
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        $article = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        return $article ? $this->createSerializer()->unserialize($article, $this->serializeFields) : null;
	}

	public function getArticlePrevious($categoryId,$createdTime)
	{
		$sql = "SELECT * FROM {$this->table} WHERE `categoryId` = ? AND createdTime < ? ORDER BY `createdTime` DESC LIMIT 1";
		$article = $this->getConnection()->fetchAssoc($sql,array($categoryId,$createdTime) ? :null);
        return $article ? $this->createSerializer()->unserialize($article, $this->serializeFields) : null;
	}
	
	public function getArticleNext($categoryId,$createdTime)
	{
		$sql = "SELECT * FROM {$this->table} WHERE  `categoryId` = ? AND createdTime > ? ORDER BY `createdTime` ASC LIMIT 1";
		$article =  $this->getConnection()->fetchAssoc($sql,array($categoryId,$createdTime) ? :null);
		return $article ? $this->createSerializer()->unserialize($article, $this->serializeFields) : null;
	}

	public function getArticleByAlias($alias)
	{
        $sql = "SELECT * FROM {$this->table} WHERE alias = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($alias)) ? : null;
        return $article ? $this->createSerializer()->unserialize($article, $this->serializeFields) : null;
	}

	public function findArticlesByCategoryIds(array $categoryIds, $start, $limit)
	{
		$this->filterStartLimit($start, $limit);

		if(empty($categoryIds)){ return array(); };

        $marks = str_repeat('?,', count($categoryIds) - 1) . '?';
        $sql = "SELECT * FROM {$this->table} WHERE categoryId in ({$marks}) ORDER BY createdTime DESC LIMIT {$start}, {$limit}";

        $artilces = $this->getConnection()->fetchAll($sql, $categoryIds);
        return $this->createSerializer()->unserializes($artilces, $this->serializeFields);
	}

	public function findArticlesCount(array $categoryIds)
	{
		if(empty($categoryIds)){ return array(); };
        $marks = str_repeat('?,', count($categoryIds) - 1) . '?';
		$sql = "SELECT COUNT(id) FROM {$this->table} WHERE categoryId in ({$marks})";

        return $this->getConnection()->fetchColumn($sql, $categoryIds);
	}

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

		$articles = $builder->execute()->fetchAll() ? : array();
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
        $article = $this->createSerializer()->serialize($article, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->table, $article);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert Article error.');
        }
        return $this->getArticle($this->getConnection()->lastInsertId());
	}

	public function waveArticle($id,$field,$diff)
	{
		$fields = array('hits');

		if (!in_array($field, $fields)) {
			throw \InvalidArgumentException(sprintf("%s字段不允许增减，只有%s才被允许增减", $field, implode(',', $fields)));
		}
		$sql = "UPDATE {$this->table} SET {$field} = {$field} + ? WHERE id = ? LIMIT 1";
		
        return $this->getConnection()->executeQuery($sql, array($diff, $id));
	}

	public function updateArticle($id, $article)
	{
        $article = $this->createSerializer()->serialize($article, $this->serializeFields);
        $this->getConnection()->update($this->table, $article, array('id' => $id));
        return $this->getArticle($id);
	}

	public function deleteArticle($id)
	{
		return $this->getConnection()->delete($this->table, array('id' => $id));
	}
    
    //@todo:sql
	public function findPublishedArticlesByTagIdsAndCount($tagIds,$count)
	{
		$sql ="SELECT * FROM {$this->table} WHERE status = 'published'";
		$length=count($tagIds);
		$sql .=" AND (";
		for ($i=0; $i < $length ; $i++) { 
			$tagId = $tagIds[$i];
			$like = "\"$tagId\"";
			$sql .= "  tagIds LIKE  '%$like%' ";
			if($i != $length-1){
				$sql .=" OR ";
			}
			
		}
		$sql .=" ) ";

		$sql .= " ORDER BY publishedTime DESC LIMIT 0, {$count}";
		
		return $this->getConnection()->fetchAll($sql);
	}	

	private function _createSearchQueryBuilder($conditions)
	{
		$conditions = array_filter($conditions);
		
		if(array_key_exists('property',$conditions)){
			$key = $conditions['property'];
			$conditions[$key] = 1;
		}

		if(array_key_exists('hasPicture',$conditions)){
			if ($conditions['hasPicture'] == true) {
				$conditions['pictureNull'] = "";		
				unset($conditions['hasPicture']);
			}
		}

		if(isset($conditions['keywords'])){
			$conditions['keywords'] = "%{$conditions['keywords']}%";
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
			->andWhere('categoryId = :categoryId')
			->andWhere('categoryId IN (:categoryIds)');

		return $builder;
	}
}