<?php

namespace Biz\Content\Service\Impl;

use Biz\BaseService;
use Biz\Content\ContentException;
use Biz\Content\Dao\ContentDao;
use Biz\Content\Service\ContentService;
use Biz\Content\Type\ContentTypeFactory;
use Biz\System\Service\LogService;
use Biz\Taxonomy\Service\CategoryService;
use Codeages\Biz\Framework\Event\Event;
use AppBundle\Common\ArrayToolkit;

class ContentServiceImpl extends BaseService implements ContentService
{
    public function getContent($id)
    {
        return $this->getContentDao()->get($id);
    }

    public function getContentByAlias($alias)
    {
        return $this->getContentDao()->getByAlias($alias);
    }

    public function searchContents($conditions, $orderBy, $start, $limit)
    {
        if (!is_array($orderBy)) {
            //老版本使用字符串表示顺序且只有一个latest,兼容orderBy为字符串时的写法
            $orderBy = array('createdTime' => 'DESC');
        }

        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getContentDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchContentCount($conditions)
    {
        $conditions = $this->prepareSearchConditions($conditions);

        return $this->getContentDao()->count($conditions);
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
            $this->createNewException(ContentException::TYPE_REQUIRED());
        }

        $type = ContentTypeFactory::create($content['type']);
        $content = $type->convert($content);
        $content = ArrayToolkit::parts($content, $type->getFields());
        $content['type'] = $type->getAlias();

        if (empty($content['title'])) {
            $this->createNewException(ContentException::TITLE_REQUIRED());
        }

        $content['title'] = $this->purifyHtml($content['title'], true);
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

        $content = $this->getContentDao()->create($content);

        $this->dispatchEvent('content.create', new Event(array('contentId' => $content['id'], 'userId' => $user['id'], 'tagIds' => $tagIds)));

        return $content;
    }

    public function updateContent($id, $fields)
    {
        $user = $this->getCurrentUser();

        $content = $this->getContent($id);

        if (empty($content)) {
            $this->createNewException(ContentException::NOTFOUND_CONTENT());
        }

        $type = ContentTypeFactory::create($content['type']);
        $fields = $type->convert($fields);
        $fields = ArrayToolkit::parts($fields, $type->getFields());

        $tagIds = empty($content['tagIds']) ? array() : $content['tagIds'];

        unset($fields['tagIds']);

        $this->getContentDao()->update($id, $fields);

        $content = $this->getContent($id);

        $this->dispatchEvent('content.update', new Event(array('contentId' => $id, 'userId' => $user['id'], 'tagIds' => $tagIds)));

        return $content;
    }

    public function trashContent($id)
    {
        $this->getContentDao()->update($id, $fields = array('status' => 'trash'));
    }

    public function deleteContent($id)
    {
        $this->getContentDao()->delete($id);

        $this->dispatchEvent('content.delete', $id);
    }

    public function publishContent($id)
    {
        $this->getContentDao()->update($id, $fields = array('status' => 'published'));
    }

    public function isAliasAvaliable($alias)
    {
        if (empty($alias)) {
            return true;
        }
        $content = $this->getContentDao()->getByAlias($alias);

        return $content ? false : true;
    }

    /**
     * @return ContentDao
     */
    protected function getContentDao()
    {
        return $this->createDao('Content:ContentDao');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
