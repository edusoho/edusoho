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

	public function getArticlePrevious($createdTime)
	{
		return $this->getArticleDao()->getArticlePrevious($createdTime);
	}

	public function getArticleNext($createdTime)
	{
		return $this->getArticleDao()->getArticleNext($createdTime);
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

		$tagNames = array_filter(explode(',', $article['tags']));
        $tags = $this->getTagService()->findTagsByNames($tagNames);
        $tagIdsArray = ArrayToolkit::column($tags, 'id');
        $article['tagIds'] = implode(',', $tagIdsArray);

        $newArticle = array();
		$match = preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i', $article['richeditorBody'], $matches);
		
		$newArticle['picture'] = $match ? $matches[1] : "";
		$newArticle['thumb'] = $article['thumb'];
		$newArticle['originalThumb'] = $article['originalThumb'];
		$newArticle['title'] = $article['title'];
		$newArticle['body'] = $article['richeditorBody'];
		$newArticle['featured'] = empty($article['featured']) ? 0 : 1;
		$newArticle['promoted'] = empty($article['promoted']) ? 0 : 1;
		$newArticle['sticky'] = empty($article['sticky']) ? 0 : 1;
		$newArticle['tagIds'] = $article['tagIds'];
		$newArticle['categoryId'] = $article['categoryId'];
		$newArticle['source'] = $article['source'];
		$newArticle['sourceUrl'] = $article['sourceUrl'];
		$newArticle['publishedTime'] = strtotime($article['publishedTime']);
		$newArticle['createdTime'] = time();
		$newArticle['updated'] = time();
		$newArticle['userId'] = $this->getCurrentUser()->id;

		$article = $this->getArticleDao()->addArticle($newArticle);
		$this->getLogService()->info('Article', 'create', "创建文章《({$article['title']})》({$article['id']})", $article);
		
		return $article;
	}

	public function updateArticle($id,$article)
	{
		$checkArticle = $this->getArticle($id);

		if(empty($checkArticle)){
			throw $this->createServiceException("文章不存在，操作失败。");
		}

		$tagNames = array_filter(explode(',', $article['tags']));
        $tags = $this->getTagService()->findTagsByNames($tagNames);
        $tagIdsArray = ArrayToolkit::column($tags, 'id');
        $article['tagIds'] = implode(',', $tagIdsArray);
        
        $editArticle = array();
		$match = preg_match('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i', $article['richeditorBody'], $matches);
		
		$editArticle['picture'] = $match ? $matches[1] : "null";
		$editArticle['thumb'] = $article['thumb'];
		$editArticle['originalThumb'] = $article['originalThumb'];
		$editArticle['title'] = $article['title'];
		$editArticle['body'] = $article['richeditorBody'];
		$editArticle['featured'] = empty($article['featured']) ? 0 : 1;
		$editArticle['promoted'] = empty($article['promoted']) ? 0 : 1;
		$editArticle['sticky'] = empty($article['sticky']) ? 0 : 1;
		$editArticle['tagIds'] = $article['tagIds'];
		$editArticle['categoryId'] = $article['categoryId'];
		$editArticle['source'] = $article['source'];
		$editArticle['sourceUrl'] = $article['sourceUrl'];
		$editArticle['publishedTime'] = strtotime($article['publishedTime']);
		$editArticle['createdTime'] = time();
		$editArticle['updated'] = time();
		$editArticle['userId'] = $this->getCurrentUser()->id;

		$article = $this->getArticleDao()->updateArticle($id,$editArticle);

		$this->getLogService()->info('Article', 'update', "修改文章《({$article['title']})》({$article['id']})", $article);
		
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
        $largeImage->resize(new Box(230, 115));
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
		$fileOriginalDirectory = str_replace("/tmp", "", $fileOriginalDirectory);
		$fileOriginalDirectory = substr($fileOriginalDirectory, 0,-1);
		
		$new_file = new File($filePath);
		$file_res = $new_file->move($fileOriginalDirectory, $fileOriginalNameNew);

        @unlink($filePath);

        $webPath = realpath($this->getKernel()->getParameter('topxia.upload.public_directory')."/../");

		return array(
				'fileOriginalName'=>$fileOriginalName,
				'fileOriginalNameNew'=>$fileOriginalNameNew,
				'fileOriginalPath'=>str_replace($webPath, "", $fileOriginalDirectory)
			);
	}

	private function prepareSearchConditions($conditions)
	{
		$conditions = array_filter($conditions);

		if (isset($conditions['includeChildren']) && $conditions['includeChildren'] == true) {
			if (isset($conditions['categoryId'])) {
				$childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
				$conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
				unset($conditions['categoryId']);
				unset($conditions['includeChindren']);
			}
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
