<?php
namespace Topxia\Service\Content\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Content\ContentService;
use Topxia\Service\Content\Type\ContentTypeFactory;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceEvent;

class ContentServiceImpl extends BaseService implements ContentService
{
	public function getContent($id)
	{
		return $this->getContentDao()->getContent($id);
	}

	public function getContentByAlias($alias)
	{
		return $this->getContentDao()->getContentByAlias($alias);
	}

	public function searchContents($conditions, $sort, $start, $limit)
	{
		switch ($sort) {
			default:
				$orderBy = array('createdTime', 'DESC');
				break;
		}
		$conditions = $this->prepareSearchConditions($conditions);
		return $this->getContentDao()->searchContents($conditions, $orderBy, $start, $limit);
	}

	public function searchContentCount($conditions)
	{
		$conditions = $this->prepareSearchConditions($conditions);
		return $this->getContentDao()->searchContentCount($conditions);
	}

	protected function prepareSearchConditions($conditions)
	{
		$conditions = array_filter($conditions);
		if (isset($conditions['categoryId'])) {
			$childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
			$conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
			unset($conditions['categoryId']);
		}
		return $conditions;
	}

	public function createContent($content)
	{
        $user = $this->getCurrentUser();

		if (empty($content['type'])) {
			throw $this->createServiceException($this->getKernel()->trans('参数缺失，创建内容失败！'));
		}

		$type = ContentTypeFactory::create($content['type']);
		$content = $type->convert($content);
		$content = ArrayToolkit::parts($content, $type->getFields());
		$content['type'] = $type->getAlias();

		if (empty($content['title'])) {
			throw $this->createServiceException($this->getKernel()->trans('内容标题不能为空，创建内容失败！'));
		}

		$content['userId'] = $this->getCurrentUser()->id;
		$content['createdTime'] = time();
		
        if (empty($content['publishedTime'])) {
			$content['publishedTime'] = $content['createdTime'];
		}

		// if(isset($content['body'])){
  //           $content['body'] = $this->purifyHtml($content['body']);
  //       }

        $tagIds = empty($content['tagIds']) ? array() : $content['tagIds'];

        unset($content['tagIds']);

		$id = $this->getContentDao()->addContent($content);

		$content = $this->getContent($id);

        $this->dispatchEvent('content.create', new ServiceEvent(array('contentId' => $id, 'userId' => $user['id'], 'tagIds' => $tagIds)));
		$this->getLogService()->info('content', 'create', "创建内容《({$content['title']})》({$content['id']})", $content);

		return $content;
	}

	public function updateContent($id, $fields)
	{  
        $user = $this->getCurrentUser();

		$content = $this->getContent($id);

		if (empty($content)) {
			throw $this->createServiceException($this->getKernel()->trans('内容不存在，更新失败！'));
		}

		$type = ContentTypeFactory::create($content['type']);
		$fields = $type->convert($fields);
		$fields = ArrayToolkit::parts($fields, $type->getFields());

        // if(isset($fields['body'])){
        //     $fields['body'] = $this->purifyHtml($fields['body']);
        // }

        $tagIds = empty($content['tagIds']) ? array() : $content['tagIds'];

        unset($fields['tagIds']);

		$this->getContentDao()->updateContent($id, $fields);

		$content = $this->getContent($id);

		$this->getLogService()->info('content', 'update', "内容《({$content['title']})》({$content['id']})更新", $content);

        $this->dispatchEvent('content.update', new ServiceEvent(array('contentId' => $id, 'userId' => $user['id'], 'tagIds' => $tagIds)));
		return $content;
	}

	public function trashContent($id)
	{
		$this->getContentDao()->updateContent($id, $fields = array('status' => 'trash'));
		$this->getLogService()->info('content', 'trash', "内容#{$id}移动到回收站");
	}

	public function deleteContent($id)
	{
		$this->getContentDao()->deleteContent($id);

        $this->dispatchEvent("content.delete", $id);
		$this->getLogService()->info('content', 'delete', "内容#{$id}永久删除");
	}

	public function publishContent($id)
	{
		$this->getContentDao()->updateContent($id, $fields = array('status' => 'published'));
		$this->getLogService()->info('content', 'publish', "内容#{$id}发布");
	}

	public function isAliasAvaliable($alias)
	{
		if (empty($alias)) {
			return true;
		}
		$content = $this->getContentDao()->getContentByAlias($alias);
		return $content ? false : true;
	}

	protected function getContentDao()
	{
		return $this->createDao('Content.ContentDao');
	}

    protected function getCategoryService()
    {
        return $this->createService('Taxonomy.CategoryService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }

}