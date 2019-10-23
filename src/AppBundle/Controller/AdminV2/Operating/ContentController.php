<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Content\Service\ContentService;
use Biz\Content\Service\FileService;
use Biz\Content\Type\ContentTypeFactory;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;

class ContentController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = array_filter($request->query->all());

        $paginator = new Paginator(
            $request,
            $this->getContentService()->searchContentCount($conditions),
            20
        );

        $contents = $this->getContentService()->searchContents(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($contents, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $categoryIds = ArrayToolkit::column($contents, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        return $this->render('admin-v2/operating/content/index.html.twig', array(
               'contents' => $contents,
                'users' => $users,
                'categories' => $categories,
               'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request, $type)
    {
        $type = ContentTypeFactory::create($type);
        if ('POST' == $request->getMethod()) {
            $content = $request->request->all();
            $content['type'] = $type->getAlias();

            $file = $request->files->get('picture');
            if (!empty($file)) {
                $record = $this->getFileService()->uploadFile('default', $file);
                $content['picture'] = $record['uri'];
            }

            $content = $this->filterEditorField($content);

            $content = $this->getContentService()->createContent($this->convertContent($content));

            return $this->render('admin-v2/operating/content/content-tr.html.twig', array(
                'content' => $content,
                'category' => $this->getCategoryService()->getCategory($content['categoryId']),
                'user' => $this->getUser(),
            ));
        }

        return $this->render('admin-v2/operating/content/content-modal.html.twig', array(
            'type' => $type,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $content = $this->getContentService()->getContent($id);
        $type = ContentTypeFactory::create($content['type']);
        $record = array();
        if ('POST' == $request->getMethod()) {
            $file = $request->files->get('picture');
            if (!empty($file)) {
                $record = $this->getFileService()->uploadFile('default', $file);
            }
            $content = $request->request->all();
            if (isset($record['uri'])) {
                $content['picture'] = $record['uri'];
            }

            $content = $this->filterEditorField($content);

            $content = $this->getContentService()->updateContent($id, $this->convertContent($content));

            return $this->render('admin-v2/operating/content/content-tr.html.twig', array(
                'content' => $content,
                'category' => $this->getCategoryService()->getCategory($content['categoryId']),
                'user' => $this->getUser(),
            ));
        }

        return $this->render('admin-v2/operating/content/content-modal.html.twig', array(
            'type' => $type,
            'content' => $content,
        ));
    }

    public function trashAction(Request $request, $id)
    {
        $this->getContentService()->trashContent($id);

        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getContentService()->deleteContent($id);

        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $id)
    {
        $this->getContentService()->publishContent($id);

        return $this->createJsonResponse(true);
    }

    public function aliasCheckAction(Request $request)
    {
        $value = $request->query->get('value');
        $thatValue = $request->query->get('that');

        if (empty($value)) {
            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        if ($value == $thatValue) {
            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        $avaliable = $this->getContentService()->isAliasAvaliable($value);
        if ($avaliable) {
            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        return $this->createJsonResponse(array('success' => false, 'message' => '该URL路径已存在'));
    }

    protected function filterEditorField($content)
    {
        if ('richeditor' == $content['editor']) {
            $content['body'] = $content['richeditor-body'];
        } elseif ('none' == $content['editor']) {
            $content['body'] = $content['noneeditor-body'];
        }

        unset($content['richeditor-body']);
        unset($content['noneeditor-body']);

        return $content;
    }

    protected function convertContent($content)
    {
        if (isset($content['tags'])) {
            $tagNames = array_filter(explode(',', $content['tags']));
            $tags = $this->getTagService()->findTagsByNames($tagNames);
            $content['tagIds'] = ArrayToolkit::column($tags, 'id');
        } else {
            $content['tagIds'] = array();
        }

        $content['publishedTime'] = empty($content['publishedTime']) ? 0 : strtotime($content['publishedTime']);

        $content['promoted'] = empty($content['promoted']) ? 0 : 1;
        $content['sticky'] = empty($content['sticky']) ? 0 : 1;
        $content['featured'] = empty($content['featured']) ? 0 : 1;

        return $content;
    }

    /**
     * @return ContentService
     */
    protected function getContentService()
    {
        return $this->createService('Content:ContentService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
