<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ArticleController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $categoryId = 0;

        if (!empty($conditions['categoryId'])) {
            $conditions['includeChildren'] = true;
            $categoryId                    = $conditions['categoryId'];
        }

        $conditions = $this->fillOrgCode($conditions);

        $paginator = new Paginator(
            $request,
            $this->getArticleService()->searchArticlesCount($conditions),
            20
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,
            'normal',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $categoryIds  = ArrayToolkit::column($articles, 'categoryId');
        $categories   = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('TopxiaAdminBundle:Article:index.html.twig', array(
            'articles'     => $articles,
            'categories'   => $categories,
            'paginator'    => $paginator,
            'categoryTree' => $categoryTree,
            'categoryId'   => $categoryId
        ));
    }

    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $formData        = $request->request->all();
            $article['tags'] = array_filter(explode(',', $formData['tags']));

            $article = $this->getArticleService()->createArticle($formData);

            $attachment = $request->request->get('attachment');
            $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $article['id'], $attachment['targetType'], $attachment['type']);
            return $this->redirect($this->generateUrl('admin_article'));
        }

        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig', array(
            'categoryTree' => $categoryTree,
            'category'     => array('id' => 0, 'parentId' => 0)
        ));
    }

    public function editAction(Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);

        if (empty($article)) {
            throw $this->createNotFoundException('文章已删除或者未发布！');
        }

        if (empty($article['tagIds'])) {
            $article['tagIds'] = array();
        }

        $tags     = $this->getTagService()->findTagsByIds($article['tagIds']);
        $tagNames = ArrayToolkit::column($tags, 'name');

        $categoryId = $article['categoryId'];
        $category   = $this->getCategoryService()->getCategory($categoryId);

        $categoryTree = $this->getCategoryService()->getCategoryTree();

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $article  = $this->getArticleService()->updateArticle($id, $formData);

            $attachment = $request->request->get('attachment');

            $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $article['id'], $attachment['targetType'], $attachment['type']);
            return $this->redirect($this->generateUrl('admin_article'));
        }

        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig', array(
            'article'      => $article,
            'categoryTree' => $categoryTree,
            'category'     => $category,
            'tagNames'     => $tagNames
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
        $id  = $request->query->get('id', null);

        if ($id) {
            array_push($ids, $id);
        }

        $result = $this->getArticleService()->deleteArticlesByIds($ids);

        if ($result) {
            return $this->createJsonResponse(array("status" => "failed"));
        } else {
            return $this->createJsonResponse(array("status" => "success"));
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
        return $this->render('TopxiaAdminBundle:Article:aticle-picture-modal.html.twig', array(
            'pictureUrl' => ""
        ));
    }

    public function pictureCropAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $files   = $this->getArticleService()->changeIndexPicture($options["images"]);

            foreach ($files as $key => $file) {
                $files[$key]["file"]['url'] = $this->get('topxia.twig.web_extension')->getFilePath($file["file"]['uri']);
            }

            return new JsonResponse($files);
        }

        $fileId                                      = $request->getSession()->get("fileId");
        list($pictureUrl, $naturalSize, $scaledSize) = $this->getFileService()->getImgFileMetaInfo($fileId, 270, 270);

        return $this->render('TopxiaAdminBundle:Article:article-picture-crop-modal.html.twig', array(
            'pictureUrl'  => $pictureUrl,
            'naturalSize' => $naturalSize,
            'scaledSize'  => $scaledSize
        ));
    }

    protected function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }

    protected function getFileService()
    {
        return $this->getServiceKernel()->createService('Content.FileService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }
}
