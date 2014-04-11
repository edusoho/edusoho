<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        $categoryIds = ArrayToolkit::column($articles, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        $categoryTree = $this->makeCategoryOptions('enabled');

        return $this->render('TopxiaAdminBundle:Article:index.html.twig',array(
        	'articles' => $articles,
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
        
        $categoryTree = $this->makeCategoryOptions('all');

        return $this->render('TopxiaAdminBundle:Article:article-modal.html.twig',array(
            'categoryTree'  => $categoryTree,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $article = $this->getArticleService()->getArticle($id);
        $tags = $this->getTagService()->findAllTags(0,$this->getTagService()->getAllTagCount());
        $categoryTree = $this->makeCategoryOptions('all');

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

    public function updatePropertyAction(Request $request,$id,$property)
    {
         $result = $this->getArticleService()->updateArticleProperty($id, $property);

          if(!$result){
            return $this->createJsonResponse(array("status" =>"failed")); 
        } else {
            return $this->createJsonResponse(array("status" =>"success")); 
        }
    }
   
    public function previewAction(Request $request,$id)
    {
        echo "previewAction";exit();
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

    public function showUploadAction(Request $request)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        // $user = $this->getUserService()->getUser($id);

        $form = $this->createFormBuilder()
            ->add('avatar', 'file')
            ->getForm();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $file = $data['avatar'];

                if (!FileToolkit::isImageFile($file)) {
                    return $this->createMessageResponse('error', '上传图片格式错误，请上传jpg, gif, png格式的文件。');
                }

                $filenamePrefix = "article_";
                $hash = substr(md5($filenamePrefix . time()), -8);
                $ext = $file->getClientOriginalExtension();
                $filename = $filenamePrefix . $hash . '.' . $ext;

                $directory = $this->container->getParameter('topxia.upload.public_directory') . '/aticle';
                $file = $file->move($directory, $filename);

                $fileName = str_replace('.', '!', $file->getFilename());

                $avatarData = $this->avatar_2($id, $fileName);

                return $this->render('TopxiaAdminBundle:User:user-avatar-crop-modal.html.twig', array(
                    'user' => $user,
                    'filename' => $fileName,
                    'pictureUrl' => $avatarData['pictureUrl'],
                    'naturalSize' => $avatarData['naturalSize'],
                    'scaledSize' => $avatarData['scaledSize']
                ));
            }
        }

        // $hasPartnerAuth = $this->getAuthService()->hasPartnerAuth();
        // if ($hasPartnerAuth) {
        //     $partnerAvatar = $this->getAuthService()->getPartnerAvatar($user['id'], 'big');
        // } else {
        //     $partnerAvatar = null;
        // }

        return $this->render('TopxiaAdminBundle:Article:aticle-picture-modal.html.twig', array(
            'form' => $form->createView(),
            // 'partnerAvatar' => $partnerAvatar,
        ));
    }


    private function avatar_2 ($id, $filename)
    {
        $filename = str_replace('!', '.', $filename);
        $filename = str_replace(array('..' , '/', '\\'), '', $filename);
        $pictureFilePath = $this->container->getParameter('topxia.upload.public_directory') . '/article/' . $filename;

        try {
            $imagine = new Imagine();
            $image = $imagine->open($pictureFilePath);
        } catch (\Exception $e) {
            @unlink($pictureFilePath);
            return $this->createMessageResponse('error', '该文件为非图片格式文件，请重新上传。');
        }

        $naturalSize = $image->getSize();
        $scaledSize = $naturalSize->widen(270)->heighten(270);
        $pictureUrl = $this->container->getParameter('topxia.upload.public_url_path') . '/article/' . $filename;

        return array(
            'naturalSize' => $naturalSize,
            'scaledSize' => $scaledSize,
            'pictureUrl' => $pictureUrl
        );
    }

    public function pictureUploadAction(Request $request)
    {
        $file = $request->files->get('picture');

        $filename = 'article_' . time() .mt_rand(0,1000000).".". $file->getClientOriginalExtension();

        $picture['filename'] = "{$this->container->getParameter('topxia.upload.public_url_path')}/article/{$filename}";
        $picture['filename'] = ltrim($picture['filename'], '/');

        $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/article";
        $file = $file->move($directory, $filename);

        $this->getLogService()->info('system', 'article_picture', "aticle上传图片", array('article_picture' => $picture['filename']));
        
        $response = array(
            'url' =>  $this->container->get('templating.helper.assets')->getUrl($picture['filename']),
        );

        return new Response(json_encode($response));
    }

    protected function makeCategoryOptions($operate_type="")
    {
        if($operate_type == "enabled"){
                $articles = $this->getArticleService()->searchArticles(
                array(),
                array('createdTime', 'DESC'),
                0,
                $this->getArticleService()->searchArticleCount(array())
            );
            $categoryIds = ArrayToolkit::column($articles, 'categoryId');
            $categoryTree = $this->getCategoryService()->findCategoriesByIds($categoryIds);
        }

        if($operate_type == "all"){
            $categoryTree = $this->getCategoryService()->findAllCategories();
        }

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