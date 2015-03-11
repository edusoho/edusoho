<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;
use Topxia\WebBundle\DataDict\ContentStatusDict;
use Topxia\WebBundle\DataDict\ContentTypeDict;
use Topxia\Service\Content\Type\ContentTypeFactory;

class ArticleCategotysController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $categoryId = 0;
        if(!empty($conditions['categoryId'])){
            $conditions['includeChildren'] = true;
            $categoryId = $conditions['categoryId'];
        }

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
        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('TopxiaAdminBundle:Operation:index.html.twig',array(
            'articles' => $articles,
            'categories' => $categories,
            'paginator' => $paginator,
            'categoryTree'  => $categoryTree,
            'categoryId'  => $categoryId
        ));
    }

    public function articleCreateAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $article = $request->request->all();
            $article['tags'] = array_filter(explode(',', $article['tags']));

            $article = $this->getArticleService()->createArticle($article);

            return $this->redirect($this->generateUrl('admin_operation'));
        }
        
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        return $this->render('TopxiaAdminBundle:Operation:article-modal.html.twig',array(
            'categoryTree'  => $categoryTree,
            'category'  => array( 'id' =>0, 'parentId' =>0)
        ));
    }

    public function articleEditAction(Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);
        if (empty($article)) {
            throw $this->createNotFoundException('文章已删除或者未发布！');
        }
        if(empty($article['tagIds'])){
            $article['tagIds'] = array();
        }

        $tags = $this->getTagService()->findTagsByIds($article['tagIds']);
        $tagNames = ArrayToolkit::column($tags, 'name');

        $categoryId = $article['categoryId'];
        $category = $this->getCategoryService()->getCategory($categoryId);

        $categoryTree = $this->getCategoryService()->getCategoryTree();

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $article = $this->getArticleService()->updateArticle($id, $formData);
            return $this->redirect($this->generateUrl('admin_operation'));
        }
        return $this->render('TopxiaAdminBundle:Operation:article-modal.html.twig',array(
            'article' => $article,
            'categoryTree'  => $categoryTree,
            'category'  => $category,
            'tagNames' => $tagNames
        ));
    }

    public function showUploadAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {

            $file = $request->files->get('picture');
            if (!FileToolkit::isImageFile($file)) {
                return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
            }

            $filenamePrefix = "article_";
            $hash = substr(md5($filenamePrefix . time()), -8);
            $ext = $file->getClientOriginalExtension();
            $filename = $filenamePrefix . $hash . '.' . $ext;

            $directory = $this->container->getParameter('topxia.upload.public_directory') . '/tmp';
            $file = $file->move($directory, $filename);
            $fileName = str_replace('.', '!', $file->getFilename());

            $articlePicture = $this->getPictureAtributes($fileName);

            return $this->render('TopxiaAdminBundle:Operation:article-picture-crop-modal.html.twig', array(
                'filename' => $fileName,
                'pictureUrl' => $articlePicture['pictureUrl'],
                'naturalSize' => $articlePicture['naturalSize'],
                'scaledSize' => $articlePicture['scaledSize']
            ));
        }

        return $this->render('TopxiaAdminBundle:Operation:aticle-picture-modal.html.twig', array(
            'pictureUrl' => "",
        ));
    }

    private function getPictureAtributes($filename)
    {
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);
        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;

        try {
            $imagine = new Imagine();
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(270)->heighten(270);
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/tmp/' . $filename;

        return array(
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
            'pictureUrl' => $pictureUrl
        );
    }

    public function pictureCropAction(Request $request)
    {

        if($request->getMethod() == 'POST') {
            $options = $request->request->all();
            $filename = $request->query->get('filename');
            $filename = str_replace('!', '.', $filename);
            $filename = str_replace(array('..' , '/', '\\'), '', $filename);
            $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/tmp/' . $filename;
            $response = $this->getArticleService()->changeIndexPicture(realpath($pictureFilePath), $options);
            return new Response(json_encode($response));
        }
    }

    public function articleDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids', array());
        $id = $request->query->get('id', null);
        
        if ($id) {
            array_push($ids, $id);
        }
        $result = $this->getArticleService()->deleteArticlesByIds($ids);
        if($result){
            return $this->createJsonResponse(array("status" =>"failed"));
        } else {
            return $this->createJsonResponse(array("status" =>"success")); 
        }
    }

    public function setArticlePropertyAction(Request $request,$id,$property)
    {
         $this->getArticleService()->setArticleProperty($id, $property);
         return $this->createJsonResponse(true); 
    }

    public function cancelArticlePropertyAction(Request $request,$id,$property)
    {
         $this->getArticleService()->cancelArticleProperty($id, $property);
         return $this->createJsonResponse(true);
    }

    public function articlePublishAction(Request $request, $id)
    {
        $this->getArticleService()->publishArticle($id);
        return $this->createJsonResponse(true);
    } 

    public function articleUnpublishAction(Request $request, $id)
    {
        $this->getArticleService()->unpublishArticle($id);
        return $this->createJsonResponse(true);
    }

    public function articleTrashAction(Request $request, $id)
    {
        $this->getArticleService()->trashArticle($id);
        return $this->createJsonResponse(true);
    }

    public function thumbRemoveAction(Request $Request,$id)
    {
        $this->getArticleService()->removeArticlethumb($id);
        return $this->createJsonResponse(true);
    }

    public function categoryIndexAction(Request $request)
    {   
        $categories = $this->getCategoryService()->getCategoryTree();
        
        return $this->render('TopxiaAdminBundle:Operation:category.index.html.twig', array(
            'categories' => $categories
        ));       
    }

    public function categoryCreateAction(Request $request)
    {

        if ($request->getMethod() == 'POST') {
            $category = $this->getCategoryService()->createCategory($request->request->all());
            return $this->renderTbody();
        }
        $category = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'parentId' => (int) $request->query->get('parentId', 0),
            'weight' => 0,
            'publishArticle' => 1,
            'seoTitle' => '',
            'seoKeyword' => '',
            'seoDesc' => '',
            'published' => 1
        );

        $categoryTree = $this->getCategoryService()->getCategoryTree();
        return $this->render('TopxiaAdminBundle:Operation:category-modal.html.twig', array(
            'category' => $category,
            'categoryTree'  => $categoryTree
        ));
    }

    public function categoryEditAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        if ($request->getMethod() == 'POST') {
            $category = $this->getCategoryService()->updateCategory($id, $request->request->all());
            return $this->renderTbody();
        }
        $categoryTree = $this->getCategoryService()->getCategoryTree();
        
        return $this->render('TopxiaAdminBundle:Operation:category-modal.html.twig', array(
            'category' => $category,
            'categoryTree'  => $categoryTree
        ));
    }

    public function checkCodeAction(Request $request)
    {
        $code = $request->query->get('value');

        $exclude = $request->query->get('exclude');

        $avaliable = $this->getCategoryService()->isCategoryCodeAvaliable($code, $exclude);
  
        if ($avaliable) {
            $response = array('success' => true, 'message' => '');
        } else {
            $response = array('success' => false, 'message' => '编码已被占用，请换一个。');
        }

        return $this->createJsonResponse($response);
    }

    public function checkParentIdAction(Request $request)
    {
        $selectedParentId = $request->query->get('value');

        $currentId = $request->query->get('currentId');

        if($currentId == $selectedParentId && $selectedParentId != 0){
            $response = array('success' => false, 'message' => '不能选择自己作为父栏目');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    public function categoryDeleteAction(Request $request, $id)
    {
        $category = $this->getCategoryService()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        if ($this->canDeleteCategory($id)) {
            return $this->createJsonResponse(array('status' => 'error', 'message'=>'此栏目有子栏目，无法删除'));
        } else {
            $this->getCategoryService()->deleteCategory($id);
            return $this->createJsonResponse(array('status' => 'success', 'message'=>'栏目已删除' ));
        }
        
    }

    private function renderTbody()
    {
        $categories = $this->getCategoryService()->getCategoryTree();
        return $this->render('TopxiaAdminBundle:Operation:tbody.html.twig', array(
            'categories' => $categories,
            'categoryTree'  => $categories
        ));
    }

    public function canDeleteCategory($id)
    {
        return $this->getCategoryService()->findCategoriesCountByParentId($id);
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Article.FileService');
    }

    private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}