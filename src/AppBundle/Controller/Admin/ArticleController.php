<?php

namespace AppBundle\Controller\Admin;

use Biz\Article\Service\ArticleService;
use Biz\File\Service\UploadFileService;
use Biz\System\Service\SettingService;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Biz\Article\ArticleException;

class ArticleController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $categoryId = 0;

        if (!empty($conditions['categoryId'])) {
            $conditions['includeChildren'] = true;
            $categoryId = $conditions['categoryId'];
        }

        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $request,
            $this->getArticleService()->countArticles($conditions),
            20
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,
            'normal',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin/article/index.html.twig', array(
            'articles' => $articles,
            'categories' => $categories,
            'paginator' => $paginator,
            'categoryTree' => $categoryTree,
            'categoryId' => $categoryId,
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();

            $article['tags'] = array_filter(explode(',', $formData['tags']));

            $article = $this->getArticleService()->createArticle($formData);

            $attachment = $request->request->get('attachment');
            $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $article['id'], $attachment['targetType'], $attachment['type']);

            return $this->redirect($this->generateUrl('admin_article'));
        }

        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('admin/article/article-modal.html.twig', array(
            'categoryTree' => $categoryTree,
            'category' => array('id' => 0, 'parentId' => 0),
        ));
    }

    public function editAction(Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);

        if (empty($article)) {
            $this->createNewException(ArticleException::NOTFOUND());
        }

        $tags = $this->getTagService()->findTagsByOwner(array(
            'ownerType' => 'article',
            'ownerId' => $id,
        ));

        $tagNames = ArrayToolkit::column($tags, 'name');

        $categoryId = $article['categoryId'];
        $category = $this->getCategoryService()->getCategory($categoryId);

        $categoryTree = $this->getCategoryService()->getCategoryTree();

        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();
            $article = $this->getArticleService()->updateArticle($id, $formData);

            $attachment = $request->request->get('attachment');

            $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $article['id'], $attachment['targetType'], $attachment['type']);

            return $this->redirect($this->generateUrl('admin_article'));
        }

        return $this->render('admin/article/article-modal.html.twig', array(
            'article' => $article,
            'categoryTree' => $categoryTree,
            'category' => $category,
            'tagNames' => $tagNames,
        ));
    }

    public function setArticlePropertyAction(Request $request, $id, $property)
    {
        $this->getArticleService()->setArticleProperty($id, $property);

        return $this->createJsonResponse(true);
    }

    public function cancelArticlePropertyAction(Request $request, $id, $property)
    {
        $this->getArticleService()->cancelArticleProperty($id, $property);

        return $this->createJsonResponse(true);
    }

    public function trashAction(Request $request, $id)
    {
        $this->getArticleService()->trashArticle($id);

        return $this->createJsonResponse(true);
    }

    public function thumbRemoveAction(Request $request, $id)
    {
        $this->getArticleService()->removeArticlethumb($id);

        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request)
    {
        $ids = $request->request->get('ids', array());
        $id = $request->query->get('id', null);

        if ($id) {
            array_push($ids, $id);
        }

        $result = $this->getArticleService()->deleteArticlesByIds($ids);

        if ($result) {
            return $this->createJsonResponse(array('status' => 'failed'));
        } else {
            return $this->createJsonResponse(array('status' => 'success'));
        }
    }

    public function publishAction(Request $request, $id)
    {
        $this->getArticleService()->publishArticle($id);

        return $this->createJsonResponse(true);
    }

    public function unpublishAction(Request $request, $id)
    {
        $this->getArticleService()->unpublishArticle($id);

        return $this->createJsonResponse(true);
    }

    public function showUploadAction(Request $request)
    {
        return $this->render('admin/article/aticle-picture-modal.html.twig', array(
            'pictureUrl' => '',
        ));
    }

    public function pictureCropAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $options = $request->request->all();
            $files = $this->getArticleService()->changeIndexPicture($options['images']);

            foreach ($files as $key => $file) {
                $files[$key]['file']['url'] = $this->get('web.twig.extension')->getFilePath($file['file']['uri']);
            }

            return new JsonResponse($files);
        }

        $fileId = $request->getSession()->get('fileId');
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        return $this->render('admin/article/article-picture-crop-modal.html.twig', array(
            'pictureUrl' => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
        ));
    }

    /**
     * @return ArticleService
     */
    protected function getArticleService()
    {
        return $this->createService('Article:ArticleService');
    }

    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getCategoryService()
    {
        return $this->createService('Article:CategoryService');
    }

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
