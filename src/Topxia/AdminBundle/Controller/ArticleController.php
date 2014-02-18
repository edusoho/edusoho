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
        $conditions = array_filter($request->query->all());

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

        return $this->render('TopxiaAdminBundle:Article:index.html.twig',array(
        	'articles' => $articles,
            'users' => $users,
            'categories' => $categories,
        	'paginator' => $paginator,
    	));
	}

	public function createAction(Request $request, $type)
	{
        $type = ArticleTypeFactory::create($type);
        if ($request->getMethod() == 'POST') {


            $article = $request->request->all();
            $article['type'] = $type->getAlias();

            $file = $request->files->get('picture');
            if(!empty($file)){
                $record = $this->getFileService()->uploadFile('default', $file);
                $article['picture'] = $record['uri'];
            }

            $article = $this->filterEditorField($article);

            $article = $this->getArticleService()->createArticle($this->convertArticle($article));
            return $this->render('TopxiaAdminBundle:Article:article-tr.html.twig',array(
                'article' => $article,
                'category' => $this->getCategoryService()->getCategory($article['categoryId']),
                'user' => $this->getCurrentUser(),
            ));
        }

        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig',array(
            'type' => $type,
        ));
	}

    public function editAction(Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);
        $type = ArticleTypeFactory::create($article['type']);
        $record = array();
        if ($request->getMethod() == 'POST') {
            $file = $request->files->get('picture');
            if(!empty($file)){
                $record = $this->getFileService()->uploadFile('default', $file);
            }
            $article = $request->request->all();
            if(isset($record['uri'])){
                $article['picture'] = $record['uri'];
            }

            $article = $this->filterEditorField($article);

            $article = $this->getArticleService()->updateArticle($id, $this->convertArticle($article));

            return $this->render('TopxiaAdminBundle:Article:article-tr.html.twig',array(
                'article' => $article,
                'category' => $this->getCategoryService()->getCategory($article['categoryId']),
                'user' => $this->getCurrentUser(),
            ));
        }

        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig',array(
            'type' => $type,
            'article' => $article,
        ));

    }

    public function trashAction(Request $request, $id)
    {
        $this->getArticleService()->trashArticle($id);
        return $this->createJsonResponse(true);
    }

    public function deleteAction(Request $request, $id)
    {
        $this->getArticleService()->deleteArticle($id);
        return $this->createJsonResponse(true);
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

    public function categoryAction(Request $request)
    {
        return $this->forward('TopxiaAdminBundle:ArticleCategory:embed', array(
            'layout' => 'TopxiaAdminBundle:Article:layout.html.twig',
        ));
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
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    private function getFileService()
    {
        return $this->getServiceKernel()->createService('Article.FileService');
    }

}