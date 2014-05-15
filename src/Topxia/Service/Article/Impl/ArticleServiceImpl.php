<?php
namespace Topxia\Service\Article\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Article\ArticleService;
use Topxia\Service\Article\Type\ArticleTypeFactory;
use Topxia\Common\ArrayToolkit;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;
use Symfony\Component\HttpFoundation\File\File;

class ArticleServiceImpl extends BaseService implements ArticleService
{
	public function getArticle($id)
	{
		return $this->getArticleDao()->getArticle($id);
	}

	public function getArticlePrevious($currentArticleId)
	{
		$article = $this->getArticle($currentArticleId);
		if(empty($article)){
			$this->createServiceException('文章内容为空,操作失败！');
		}
		$createdTime = $article['createdTime'];
		$categoryId = $article['categoryId'];
		$category = $this->getCategoryService()->getCategory($categoryId);

		if(empty($category)){
			$this->createServiceException('文章分类不存在,操作失败！');
		}

		return $this->getArticleDao()->getArticlePrevious($categoryId,$createdTime);
	}

	public function getArticleNext($currentArticleId)
	{
		$article = $this->getArticle($currentArticleId);

		if(empty($article)){
			$this->createServiceException('文章内容为空,操作失败！');
		}
		$createdTime = $article['createdTime'];
		$categoryId = $article['categoryId'];
		$category = $this->getCategoryService()->getCategory($categoryId);
		
		if(empty($category)){
			$this->createServiceException('文章分类不存在,操作失败！');
		}

		return $this->getArticleDao()->getArticleNext($categoryId,$createdTime);
	}

	public function getArticleByAlias($alias)
	{
		return ArticleSerialize::unserialize($this->getArticleDao()->getArticleByAlias($alias));
	}

	public function findArticlesByCategoryIds(array $categoryIds, $start, $limit)
	{
		return $this->getArticleDao()->findArticlesByCategoryIds($categoryIds, $start, $limit);
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
		if(empty($article)){
			$this->createServiceException("文章内容为空，创建文章失败！");
		}

		$article = $this->filterArticleFields($article, 'add');
		$article = $this->getArticleDao()->addArticle($article);

		$this->getLogService()->info('article', 'create', "创建文章《({$article['title']})》({$article['id']})");
		
		return $article;
	}

	public function updateArticle($id,$article)
	{
		$checkArticle = $this->getArticle($id);

		if(empty($checkArticle)){
			throw $this->createServiceException("文章不存在，操作失败。");
		}

		$article = $this->filterArticleFields($article);

		$article = $this->getArticleDao()->updateArticle($id, $article);

		$this->getLogService()->info('Article', 'update', "修改文章《({$article['title']})》({$article['id']})");
		
		return $article;
	}

	public function hitArticle($id)
	{
		$checkArticle = $this->getArticle($id);

		if(empty($checkArticle)){
			throw $this->createServiceException("文章不存在，操作失败。");
		}

		$this->getArticleDao()->waveArticle($id, 'hits', +1);
	}

	public function setArticleProperty($id, $property)
	{
		$article = $this->getArticleDao()->getArticle($id);

		if(empty($property)){
			throw $this->createServiceException('属性{$property}不存在，更新失败！');
		}

		$propertyVal = 1;
		$this->getArticleDao()->updateArticle($id,array("{$property}"=>$propertyVal));

		$this->getLogService()->info('setArticleProperty', 'updateArticleProperty', "文章#{$id},$article[$property]=>{$propertyVal}");
		
		return $propertyVal;
	}

	public function cancelArticleProperty($id, $property)
	{
		$article = $this->getArticleDao()->getArticle($id);

		if(empty($property)){
			throw $this->createServiceException('属性{$property}不存在，更新失败！');
		}

		$propertyVal = 0;
		$this->getArticleDao()->updateArticle($id,array("{$property}"=>$propertyVal));

		$this->getLogService()->info('cancelArticleProperty', 'updateArticleProperty', "文章#{$id},$article[$property]=>{$propertyVal}");
		
		return $propertyVal;
	}

	public function trashArticle($id)
	{
		$checkArticle = $this->getArticle($id);

		if(empty($checkArticle)){
			throw $this->createServiceException("文章不存在，操作失败。");
		}

		$this->getArticleDao()->updateArticle($id, $fields = array('status' => 'trash'));
		$this->getLogService()->info('Article', 'trash', "文章#{$id}移动到回收站");
	}

	public function removeArticlethumb($id)
	{
		$checkArticle = $this->getArticle($id);

		if(empty($checkArticle)){
			throw $this->createServiceException("文章不存在，操作失败。");
		}

		$this->getArticleDao()->updateArticle($id, $fields = array('thumb' => ''));
		$this->getArticleDao()->updateArticle($id, $fields = array('originalThumb' => ''));
		$this->getLogService()->info('Article', 'removeThumb', "文章#{$id}removeThumb");
	}

	public function deleteArticle($id)
	{
		$checkArticle = $this->getArticle($id);
			
		if(empty($checkArticle)){
			throw $this->createServiceException("文章不存在，操作失败。");
		}

		$res = $this->getArticleDao()->deleteArticle($id);
		$this->getLogService()->info('Article', 'delete', "文章#{$id}永久删除");
		return true;
	}

	public function deleteArticlesByIds($ids)
	{
		if(count($ids) == 1){
			$this->deleteArticle($ids[0]);
		}else{
			foreach ($ids as $id) {
				$this->deleteArticle($id);
			}
		}
	}

	public function publishArticle($id)
	{
		$this->getArticleDao()->updateArticle($id, $fields = array('status' => 'published'));
		$this->getLogService()->info('Article', 'publish', "文章#{$id}发布");
	}

	public function unpublishArticle($id)
	{
		$this->getArticleDao()->updateArticle($id, $fields = array('status' => 'unpublished'));
		$this->getLogService()->info('Article', 'unpublish', "文章#{$id}未发布");
	}

	public function changeIndexPicture($filePath, $options)
	{
        $pathinfo = pathinfo($filePath);
        $imagine = new Imagine();
        $rawImage = $imagine->open($filePath);
        $largeImage = $rawImage->copy();

        $largeImage->crop(new Point($options['x'], $options['y']), new Box($options['width'], $options['height']));
        $largeImage->resize(new Box(216, 120));
        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeImage->save($largeFilePath, array('quality' => 90));

        $largeFilePath = "{$pathinfo['dirname']}/{$pathinfo['filename']}_large.{$pathinfo['extension']}";
        $largeFileRecord = $this->getFileService()->uploadFile('article', new File($largeFilePath));

		$uri = $largeFileRecord['uri'];
		$fileOriginalName = basename($uri);
		$fileOriginalExtension = pathinfo($uri,PATHINFO_EXTENSION);
		$fileOriginalNameNew = str_replace(".{$fileOriginalExtension}", "_orig.{$fileOriginalExtension}", $fileOriginalName);

		$fileOriginalPath = str_replace(array('public://',"{$fileOriginalName}"),'', $uri);
		$fileOriginalDirectory =$pathinfo['dirname'] . '/' . $fileOriginalPath;
		$fileOriginalDirectory = str_replace(array("/tmp", '\tmp'), "", $fileOriginalDirectory);
		$fileOriginalDirectory = substr($fileOriginalDirectory, 0,-1);
		
		$new_file = new File($filePath);
		$file_res = $new_file->move($fileOriginalDirectory, $fileOriginalNameNew);

        @unlink($filePath);

        $webPath = realpath($this->getKernel()->getParameter('topxia.upload.public_directory'))."/";
		return array(
				'fileOriginalName'=>$fileOriginalName,
				'fileOriginalNameNew'=>$fileOriginalNameNew,
				'fileOriginalPath'=>str_replace($webPath, "", $fileOriginalDirectory)
			);
	}

	private function filterArticleFields($fields, $mode = 'update')
	{
        $article = array();

		$match = preg_match('/<\s*img.+?src\s*=\s*[\"|\'](.*?)[\"|\']/i', $fields['body'], $matches);
		$article['picture'] = $match ? $matches[1] : "";

		$article['thumb'] = $fields['thumb'];
		$article['originalThumb'] = $fields['originalThumb'];
		$article['title'] = $fields['title'];
		$article['body'] = $fields['body'];
		$article['featured'] = empty($fields['featured']) ? 0 : 1;
		$article['promoted'] = empty($fields['promoted']) ? 0 : 1;
		$article['sticky'] = empty($fields['sticky']) ? 0 : 1;
		$article['categoryId'] = $fields['categoryId'];
		$article['source'] = $fields['source'];
		$article['sourceUrl'] = $fields['sourceUrl'];
		$article['publishedTime'] = strtotime($fields['publishedTime']);
		$article['updatedTime'] = time();
		
		if (!empty($fields['tags']) && !is_array($fields['tags'])) {
			$fields['tags'] = explode(",", $fields['tags']);
	        $article['tagIds'] = ArrayToolkit::column($this->getTagService()->findTagsByNames($fields['tags']), 'id');
		}

		if ($mode == 'add') {
	        $article['tagIds'] = ArrayToolkit::column($this->getTagService()->findTagsByNames($fields['tags']), 'id');
			$article['status'] = 'published';
			$article['userId'] = $this->getCurrentUser()->id;
			$article['createdTime'] = time();
		}

		return $article;
	}

	private function prepareSearchConditions($conditions)
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

	private function filterSort($sort)
	{
		switch ($sort) {

			case 'created':
				$orderBys = array(
					array('createdTime', 'DESC'),
				);
				break;

			case 'published':
				$orderBys = array(
					array('sticky', 'DESC'),
					array('publishedTime', 'DESC'),
				);
				break;

			case 'normal':
				$orderBys = array(
					array('publishedTime', 'DESC'),
				);
				break;

			case 'popular':
				$orderBys = array(
					array('hits', 'DESC'),
				);
				break;

			default:
				throw $this->createServiceException('参数sort不正确。');
		}
		return $orderBys;
	}

	private function getArticleDao()
	{
		return $this->createDao('Article.ArticleDao');
	}

    private function getCategoryService()
    {
        return $this->createService('Article.CategoryService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getFileService()
    {
        return $this->createService('Content.FileService');
    }

    private function getTagService()
    {
        return $this->createService('Taxonomy.TagService');
    }
}
