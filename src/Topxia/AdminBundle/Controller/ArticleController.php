<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\DataDict\ArticleStatusDict;
use Topxia\WebBundle\DataDict\ArticleTypeDict;
use Topxia\Service\Article\Type\ArticleTypeFactory;

class ArticleController extends BaseController
{

	public function indexAction(Request $request)
	{
        $conditions = $request->query->all();

        $paginator = new Paginator(
            $request,
            $this->getArticleService()->searchArticleCount($conditions),
            20
        );

        $articles = $this->getArticleService()->searchArticles(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $userIds = ArrayToolkit::column($articles, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        $categoryTree = $this->makeCategoryOptions($categories);

        return $this->render('TopxiaAdminBundle:Article:index.html.twig',array(
        	'articles' => $articles,
            'users' => $users,
            'categories' => $categories,
        	'paginator' => $paginator,
            'categoryTree'  => $categoryTree
    	));
	}

    public function createAction(Request $request)
    {
        $categoryTree = $this->getCategoryService()->getCategoryTree();
        if($request->getMethod() == 'POST'){
            $content = $request->request->all();
            $article = $this->getArticleService()->createArticle($content);
        }

        $categoryTree = $this->makeCategoryOptions($categoryTree);
        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig',array(
            'categoryTree'  => $categoryTree,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);

        $tags = $this->getTagService()->findAllTags(0,$this->getTagService()->getAllTagCount());
        $categoryTree = $this->getCategoryService()->getCategoryTree();
        $categoryTree = $this->makeCategoryOptions($categoryTree);

        if ($request->getMethod() == 'POST') {

            $formData = $request->request->all();

            $article = $this->getArticleService()->updateArticle($id, $formData);
            return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig',array(
                'article' => $article,
                'categoryTree'  => $categoryTree,
                'category' => $this->getCategoryService()->getCategory($article['categoryId']),
                'tags' => ArrayToolkit::column($tags, 'name')
            ));
        }
     
        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig',array(
            'article' => $article,
            'categoryTree'  => $categoryTree,
            'tags' => ArrayToolkit::column($tags, 'name')
        ));

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

    public function pictureUploadAction(Request $request)
    {
        $file = $request->files->get('picture');
        // if (!FileToolkit::isIcoFile($file)) {
        //     throw $this->createAccessDeniedException('图标格式不正确！');
        // }
        $filename = 'pic_' . time() . '.' . $file->getClientOriginalExtension();
        var_dump($filename);
        
        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/aticle";
        $file = $file->move($directory, $filename);

        // $site = $this->getSettingService()->get('site', array());

        // $site['favicon'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";
        // $site['favicon'] = ltrim($site['favicon'], '/');

        // $this->getSettingService()->set('site', $site);

        // $this->getLogService()->info('system', 'update_settings', "上传文章图片", array('favicon' => $site['favicon']));

        $response = array(
            'path' => $site['favicon'],
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($site['favicon']),
        );

        return new Response(json_encode($response));
    }

    protected function makeCategoryOptions($categoryTree=array())
    {
        $options = array();
        foreach ($categoryTree as $category) {
            $options[$category['id']] = $category['name'];
        }

        return $options;
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

}