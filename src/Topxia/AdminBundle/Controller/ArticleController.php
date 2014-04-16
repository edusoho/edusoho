<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\WebBundle\DataDict\ArticleStatusDict;
use Topxia\WebBundle\DataDict\ArticleTypeDict;
use Topxia\Service\Article\Type\ArticleTypeFactory;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\ImageInterface;

class ArticleController extends BaseController
{

	public function indexAction(Request $request)
	{
        $conditions = $request->query->all();
        $paginator = new Paginator(
            $request,
            $this->getArticleService()->searchArticlesCount($conditions),
            20
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categoryTree = $this->getCategoryService()->getCategoryTree();
        $category = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'pagesize' => '10',
            'parentId' => (int) $request->query->get('parentId', 0),
            'weight' => 0,
            'publishArticle' => 1,
            'seoTitle' => '',
            'seoKeyword' => '',
            'seoDesc' => '',
            'published' => 1
        );

        return $this->render('TopxiaAdminBundle:Article:index.html.twig',array(
        	'articles' => $articles,
            'categories' => $categories,
        	'paginator' => $paginator,
            'categoryTree'  => $categoryTree,
            'category'  => $category
    	));
	}

    public function createAction(Request $request)
    {
        if($request->getMethod() == 'POST'){
            $content = $request->request->all();
            $article = $this->getArticleService()->createArticle($content);
            return $this->redirect($this->generateUrl('admin_article'));
        }
        
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        $category = array(
            'id' => 0,
            'name' => '',
            'code' => '',
            'pagesize' => '10',
            'parentId' => (int) $request->query->get('parentId', 0),
            'weight' => 0,
            'publishArticle' => 1,
            'seoTitle' => '',
            'seoKeyword' => '',
            'seoDesc' => '',
            'published' => 1
        );

        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig',array(
            'categoryTree'  => $categoryTree,
            'category'  => $category
        ));
    }

    public function editAction(Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);

        if (empty($article)) {
            throw $this->createNotFoundException('文章已删除或者未发布！');
        }

        $tagNamesStr = empty($article['tagIds']) ? "" : $this->getTagNamesByTagIdsStr($article['tagIds']);

        $tags = $this->getTagService()->findAllTags(0,$this->getTagService()->getAllTagCount());
        $categoryTree = $this->getCategoryService()->getCategoryTree();

        $categoryId = $article['categoryId'];
        $category = $this->getCategoryService()->getCategory($categoryId);
        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $article = $this->getArticleService()->updateArticle($id, $formData);
            return $this->redirect($this->generateUrl('admin_article'));
        }
        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig',array(
            'article' => $article,
            'categoryTree'  => $categoryTree,
            'category'  => $category,
            'tags' => ArrayToolkit::column($tags, 'name'),
            'tagNamesStr' => $tagNamesStr
        ));
    }

    public function previewAction(Request $request,$id)
    {
        return $this->forward('TopxiaWebBundle:Article:detail', array('id' => $id));
    }

    public function setArticlePropertyAction(Request $request,$id,$property)
    {
         $result = $this->getArticleService()->setArticleProperty($id, $property);
         return $this->createJsonResponse(array("status" =>"success")); 
    }

    public function cancelArticlePropertyAction(Request $request,$id,$property)
    {
         $result = $this->getArticleService()->cancelArticleProperty($id, $property);
         return $this->createJsonResponse(array("status" =>"default")); 
    }

    public function trashAction(Request $request, $id)
    {
        $this->getArticleService()->trashArticle($id);
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
        if($result){
            return $this->createJsonResponse(array("status" =>"failed"));
        } else {
            return $this->createJsonResponse(array("status" =>"success")); 
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

    public function settingAction(Request $request)
    {   
        $articleSetting = $this->getSettingService()->get('article', array());

        $default = array(
            'name' => '资讯频道',
            'pageNums' => 20
        );

        $articleSetting = array_merge($default, $articleSetting);

        if ($request->getMethod() == 'POST') {
            $articleSetting = $request->request->all();
            $this->getSettingService()->set('article', $articleSetting);
            $this->getLogService()->info('article', 'update_settings', "更新资讯频道设置", $articleSetting);
            $this->setFlashMessage('success', '资讯频道设置已保存！');
        };

        return $this->render('TopxiaAdminBundle:Article:setting.html.twig', array(
            'articleSetting' => $articleSetting
        ));
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

        $avaliable = $this->getArticleService()->isAliasAvaliable($value);
        if ($avaliable) {
            return $this->createJsonResponse(array('success' => true, 'message' => ''));
        }

        return $this->createJsonResponse(array('success' => false, 'message' => '该URL路径已存在'));
    }

    public function showUploadAction(Request $request)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

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
                return $this->render('TopxiaAdminBundle:Article:article-picture-crop-modal.html.twig', array(
                    'filename' => $fileName,
                    'pictureUrl' => $articlePicture['pictureUrl'],
                    'naturalSize' => $articlePicture['naturalSize'],
                    'scaledSize' => $articlePicture['scaledSize']
                ));
        }

        return $this->render('TopxiaAdminBundle:Article:aticle-picture-modal.html.twig', array(
            'pictureUrl' => "",
        ));
    }

    private function getTagNamesByTagIdsStr($tagIdsStr)
    {
        $tagIds = explode(",", $tagIdsStr);
        $tags = $this->getTagService()->findTagsByIds($tagIds);
        $tagNamesArray = ArrayToolkit::column($tags, 'name');
        $tagNamesStr = implode(",", $tagNamesArray);
        return $tagNamesStr;
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
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
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

    private function filterEditorField($article)
    {
        if($article['editor'] == 'richeditor'){
            $article['body'] = $article['richeditor-body'];
        } elseif ($article['editor'] == 'none') {
            $article['body'] = $article['noneeditor-body'];
        }

        unset($article['richeditor-body']);
        unset($article['noneeditor-body']);
        return $article;
    }

    private function convertArticle($article)
    {
        if (isset($article['tags'])) {
            $tagNames = array_filter(explode(',', $article['tags']));
            $tags = $this->getTagService()->findTagsByNames($tagNames);
            $article['tagIds'] = ArrayToolkit::column($tags, 'id');
        } else {
            $article['tagIds'] = array();
        }

        $article['publishedTime'] = empty($article['publishedTime']) ? 0 : strtotime($article['publishedTime']);

        $article['promoted'] = empty($article['promoted']) ? 0 : 1;
        $article['sticky'] = empty($article['sticky']) ? 0 : 1;
        $article['featured'] = empty($article['featured']) ? 0 : 1;

        return $article;
    }

    private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    private function getCategoryService2()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Article.CategoryService');
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