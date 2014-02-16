<?php
namespace Topxia\Service\Article\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Article\ArticleService;
use Topxia\Service\Article\Type\ArticleTypeFactory;
use Topxia\Common\ArrayToolkit;

class ArticleServiceImpl extends BaseService implements ArticleService
{
	public function getArticle($id)
	{
		return ArticleSerialize::unserialize($this->getArticleDao()->getArticle($id));
	}

	public function getArticleByAlias($alias)
	{
		return ArticleSerialize::unserialize($this->getArticleDao()->getArticleByAlias($alias));
	}

	public function searchArticles($conditions, $sort, $start, $limit)
	{
		switch ($sort) {
			default:
				$orderBy = array('createdTime', 'DESC');
				break;
		}
		$conditions = $this->prepareSearchConditions($conditions);
		return $this->getArticleDao()->searchArticles($conditions, $orderBy, $start, $limit);
	}

	public function searchArticleCount($conditions)
	{
		$conditions = $this->prepareSearchConditions($conditions);
		return $this->getArticleDao()->searchArticleCount($conditions);
	}

	private function prepareSearchConditions($conditions)
	{
		$conditions = array_filter($conditions);
		if (isset($conditions['categoryId'])) {
			$childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
			$conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
			unset($conditions['categoryId']);
		}
		return $conditions;
	}

	public function createArticle($Article)
	{
		if (empty($Article['type'])) {
			throw $this->createServiceException('参数缺失，创建内容失败！');
		}

		$type = ArticleTypeFactory::create($Article['type']);
		$Article = $type->convert($Article);
		$Article = ArrayToolkit::parts($Article, $type->getFields());
		$Article['type'] = $type->getAlias();

		if (empty($Article['title'])) {
			throw $this->createServiceException('内容标题不能为空，创建内容失败！');
		}

		$Article['userId'] = $this->getCurrentUser()->id;
		$Article['createdTime'] = time();
		
        if (empty($Article['publishedTime'])) {
			$Article['publishedTime'] = $Article['createdTime'];
		}

		// if(isset($Article['body'])){
  //           $Article['body'] = $this->purifyHtml($Article['body']);
  //       }


		$id = $this->getArticleDao()->addArticle(ArticleSerialize::serialize($Article));

		$Article = $this->getArticle($id);

        $this->getLogService()->info('Article', 'create', "创建内容《({$Article['title']})》({$Article['id']})", $Article);

		return $Article;
	}

	public function updateArticle($id, $fields)
	{
		$Article = $this->getArticle($id);
		if (empty($Article)) {
			throw $this->createServiceException('内容不存在，更新失败！');
		}

		$type = ArticleTypeFactory::create($Article['type']);
		$fields = $type->convert($fields);
		$fields = ArrayToolkit::parts($fields, $type->getFields());

        // if(isset($fields['body'])){
        //     $fields['body'] = $this->purifyHtml($fields['body']);
        // }

		$this->getArticleDao()->updateArticle($id, ArticleSerialize::serialize($fields));

		$Article = $this->getArticle($id);

		$this->getLogService()->info('Article', 'update', "内容《({$Article['title']})》({$Article['id']})更新", $Article);

		return $Article;
	}

	public function trashArticle($id)
	{
		$this->getArticleDao()->updateArticle($id, $fields = array('status' => 'trash'));
		$this->getLogService()->info('Article', 'trash', "内容#{$id}移动到回收站");
	}

	public function deleteArticle($id)
	{
		$this->getArticleDao()->deleteArticle($id);
		$this->getLogService()->info('Article', 'delete', "内容#{$id}永久删除");
	}

	public function publishArticle($id)
	{
		$this->getArticleDao()->updateArticle($id, $fields = array('status' => 'published'));
		$this->getLogService()->info('Article', 'publish', "内容#{$id}发布");
	}

	public function isAliasAvaliable($alias)
	{
		if (empty($alias)) {
			return true;
		}
		$Article = $this->getArticleDao()->getArticleByAlias($alias);
		return $Article ? false : true;
	}

	private function getArticleDao()
	{
		return $this->createDao('Article.ArticleDao');
	}

    private function getCategoryService()
    {
        return $this->createService('Taxonomy.CategoryService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}



class ArticleSerialize
{
    public static function serialize(array &$course)
    {
    	if (isset($course['tagIds'])) {
    		if (is_array($course['tagIds']) and !empty($course['tagIds'])) {
    			$course['tagIds'] = '|' . implode('|', $course['tagIds']) . '|';
    		} else {
    			$course['tagIds'] = '';
    		}
    	}
        return $course;
    }

    public static function unserialize(array $course = null)
    {
    	if (empty($course)) {
    		return $course;
    	}

		$course['tagIds'] = empty($course['tagIds']) ? array() : explode('|', trim($course['tagIds'], '|'));

		return $course;
    }

    public static function unserializes(array $courses)
    {
    	return array_map(function($course) {
    		return ArticleSerialize::unserialize($course);
    	}, $courses);
    }
}