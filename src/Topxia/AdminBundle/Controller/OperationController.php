<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Imagine\Gd\Imagine;

class OperationController extends BaseController
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

    public function groupIndexAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'status'=>'',
            'title'=>'',
            'ownerName'=>'',
        );

        if(!empty($fields)){
            $conditions =$fields;
        } 

        $paginator = new Paginator(
            $this->get('request'),
            $this->getGroupService()->searchGroupsCount($conditions),
            10
        );

        $groupinfo=$this->getGroupService()->searchGroups(
                $conditions,
                array('createdTime','desc'),
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

        $ownerIds =  ArrayToolkit::column($groupinfo, 'ownerId');
        $owners = $this->getUserService()->findUsersByIds($ownerIds);

        return $this->render('TopxiaAdminBundle:Operation:group.index.html.twig',array(
            'groupinfo'=>$groupinfo,
            'owners'=>$owners,
            'paginator' => $paginator));
    }

    public function  closeGroupAction($id)
    {
        $this->getGroupService()->closeGroup($id);

        $groupinfo=$this->getGroupService()->getGroup($id);
        
        $owners=$this->getUserService()->findUsersByIds(array('0'=>$groupinfo['ownerId']));

        return $this->render('TopxiaAdminBundle:Operation:group-tr.html.twig', array(
            'group' => $groupinfo,
            'owners'=>$owners,
        ));
    }

    public function openGroupAction($id)
    {
        $this->getGroupService()->openGroup($id);

        $groupinfo=$this->getGroupService()->getGroup($id);

        $owners=$this->getUserService()->findUsersByIds(array('0'=>$groupinfo['ownerId']));

        return $this->render('TopxiaAdminBundle:Operation:group-tr.html.twig', array(
            'group' => $groupinfo,
            'owners'=>$owners,
        ));
    }

    public function transferGroupAction(Request $request,$groupId)
    {
        $data=$request->request->all();

        $user=$this->getUserService()->getUserByNickname($data['user']['nickname']);

        $group=$this->getGroupService()->getGroup($groupId);

        $ownerId=$group['ownerId'];

        $member=$this->getGroupService()->getMemberByGroupIdAndUserId($groupId,$ownerId);

        $this->getGroupService()->updateMember($member['id'],array('role'=>'member'));

        $this->getGroupService()->updateGroup($groupId,array('ownerId'=>$user['id']));

        $member=$this->getGroupService()->getMemberByGroupIdAndUserId($groupId,$user['id']);

        if($member){
            $this->getGroupService()->updateMember($member['id'],array('role'=>'owner'));
        }else{
            $this->getGroupService()->addOwner($groupId,$user['id']);
        }

        return new Response("success");
    }

    public function groupThreadAction(Request $request)
    {
        $fields = $request->query->all();

        $conditions = array(
            'status'=>'',
            'title'=>'',
            'groupName'=>'',
            'userName'=>'',
        );

        if(!empty($fields)){
            $conditions =$fields;
        }
        
        $paginator = new Paginator(
            $this->get('request'),
            $this->getThreadService()->searchThreadsCount($conditions),
            10
        );

        $threadinfo=$this->getThreadService()->searchThreads(
            $conditions,
            $this->filterSort('byCreatedTime'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $memberIds = ArrayToolkit::column($threadinfo, 'userId');

        $owners = $this->getUserService()->findUsersByIds($memberIds);

        $groupIds =  ArrayToolkit::column($threadinfo, 'groupId');


        $group=$this->getGroupService()->getGroupsByIds($groupIds);

        return $this->render('TopxiaAdminBundle:Operation:group.thread.html.twig',array(
            'threadinfo'=>$threadinfo,
            'owners'=>$owners,
            'group'=>$group,
            'paginator' => $paginator));
    }

    public function batchDeleteThreadAction(Request $request)
    {
        $threadIds=$request->request->all();
        foreach ($threadIds['ID'] as $threadId) {
            $this->getThreadService()->deleteThread($threadId); 
        }
        return new Response('success');
    }

    public function removeEliteAction($threadId)
    {
        return $this->postAction($threadId,'removeElite');
    }

    public function setEliteAction($threadId)
    {
        return $this->postAction($threadId,'setElite');
    }

    public function removeStickAction($threadId)
    {
        return $this->postAction($threadId,'removeStick');
    }

    public function setStickAction($threadId)
    {
        return $this->postAction($threadId,'setStick');
    }

    public function closeThreadAction($threadId)
    {
        return $this->postAction($threadId,'closeThread');
    }

    public function openThreadAction($threadId)
    {
        return $this->postAction($threadId,'openThread');
    }

    public function deleteThreadAction($threadId)
    {   
        $thread=$this->getThreadService()->getThread($threadId);
        $threadUrl = $this->generateUrl('group_thread_show', array('id'=>$thread['groupId'],'threadId'=>$thread['id']), true);
        $this->getThreadService()->deleteThread($threadId);
        $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被管理员删除。");
        return $this->createJsonResponse('success');

    }

     private function postAction($threadId,$action)
    {
        $thread=$this->getThreadService()->getThread($threadId);
        $threadUrl = $this->generateUrl('group_thread_show', array('id'=>$thread['groupId'],'threadId'=>$thread['id']), true);
        
        if($action=='setElite'){
           $this->getThreadService()->setElite($threadId); 
           $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被设为精华。"); 
        }
        if($action=='removeElite'){
           $this->getThreadService()->removeElite($threadId); 
           $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被取消精华。"); 
        }
        if($action=='setStick'){
           $this->getThreadService()->setStick($threadId); 
           $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被置顶。"); 
        }
        if($action=='removeStick'){
           $this->getThreadService()->removeStick($threadId); 
           $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被取消置顶。");
        }
        if($action=='closeThread'){
           $this->getThreadService()->closeThread($threadId); 
           $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被关闭。");
        }
        if($action=='openThread'){
           $this->getThreadService()->openThread($threadId); 
           $this->getNotifiactionService()->notify($thread['userId'],'default',"您的话题<a href='{$threadUrl}' target='_blank'><strong>“{$thread['title']}”</strong></a>被打开。");
        }

        $thread=$this->getThreadService()->getThread($threadId);

        $owners=$this->getUserService()->findUsersByIds(array('0'=>$thread['userId']));

        $group=$this->getGroupService()->getGroupsByIds(array('0'=>$thread['groupId']));


        return $this->render('TopxiaAdminBundle:Operation:thread-table-tr.html.twig', array(
            'thread' => $thread,
            'owners'=>$owners,
            'group'=>$group,
        ));

    }

    public function canDeleteCategory($id)
    {
        return $this->getCategoryService()->findCategoriesCountByParentId($id);
    }

    public function blockIndexAction(Request $request)
    {
        $paginator = new Paginator(
            $this->get('request'),
            $this->getBlockService()->searchBlockCount(),
            20
        );

        $findedBlocks = $this->getBlockService()->searchBlocks($paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        
        $latestBlockHistory = $this->getBlockService()->getLatestBlockHistory();
        $latestUpdateUser = $this->getUserService()->getUser($latestBlockHistory['userId']);

        return $this->render('TopxiaAdminBundle:Operation:block.index.html.twig', array(
            'blocks'=>$findedBlocks,
            'latestUpdateUser'=>$latestUpdateUser,
            'paginator' => $paginator
        ));
    }

    public function blockCreateAction(Request $request)
    {
        
        if ('POST' == $request->getMethod()) {
            $block = $this->getBlockService()->createBlock($request->request->all());
            $user = $this->getCurrentUser();
            $html = $this->renderView('TopxiaAdminBundle:Operation:block-tr.html.twig', array('block' => $block,'latestUpdateUser'=>$user));
            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        $editBlock = array(
            'id' => 0,
            'title' => '',
            'code' => '',
            'mode' => 'html',
            'template' => ''
        );

        return $this->render('TopxiaAdminBundle:Operation:block-modal.html.twig', array(
            'editBlock' => $editBlock
        ));
    }

    public function blockUpdateAction(Request $request, $block)
    {
        if (is_numeric(($block))) {
            $block = $this->getBlockService()->getBlock($block);
        } else {
            $block = $this->getBlockService()->getBlockByCode($block);
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getBlockService()->findBlockHistoryCountByBlockId($block['id']),
            5
        );
        
        $templateData = array();
        $templateItems = array();
        if ($block['mode'] == 'template') {
            $templateItems = $this->getBlockService()->generateBlockTemplateItems($block);
            $templateData = json_decode($block['templateData'],true);
        } 

        $blockHistorys = $this->getBlockService()->findBlockHistorysByBlockId(
            $block['id'], 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        foreach ($blockHistorys as &$blockHistory) {
            $blockHistory['templateData'] = json_decode($blockHistory['templateData'],true);
        }

        $historyUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($blockHistorys, 'userId'));

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();

            $templateData = array();
            if ($block['mode'] == 'template') {
                $template = $block['template'];
                
                $template = str_replace(array("(( "," ))","((  ","  )"),array("((","))","((","))"),$template); 
                
                $content = "";
                
                foreach ($fields as $key => $value) {   
                    $content = str_replace('(('.$key.'))', $value, $template);
                    break;
                };
                foreach ($fields as $key => $value) {   
                    $content = str_replace('(('.$key.'))', $value, $content);
                }
                $templateData = $fields;
                $fields = "";
                $fields['content'] = $content;
                $fields['templateData'] = json_encode($templateData);
            }
            
            $block = $this->getBlockService()->updateBlock($block['id'], $fields);
            $latestBlockHistory = $this->getBlockService()->getLatestBlockHistory();
            $latestUpdateUser = $this->getUserService()->getUser($latestBlockHistory['userId']);
            $html = $this->renderView('TopxiaAdminBundle:Operation:block-tr.html.twig', array(
                'block' => $block, 'latestUpdateUser'=>$latestUpdateUser
            ));
            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));          
        }

        return $this->render('TopxiaAdminBundle:Operation:block-update-modal.html.twig', array(
            'block' => $block,
            'blockHistorys' => $blockHistorys,
            'historyUsers' => $historyUsers,
            'paginator' => $paginator,
            'templateItems' => $templateItems,
            'templateData' => $templateData
        ));
    }

    public function previewAction(Request $request, $id)
    {
        $blockHistory = $this->getBlockService()->getBlockHistory($id);
        return $this->render('TopxiaAdminBundle:Operation:blockhistory-preview.html.twig', array(
            'blockHistory'=>$blockHistory
        ));
    }


    public function blockEditAction(Request $request, $block)
    {
        $block = $this->getBlockService()->getBlock($block);

        if ('POST' == $request->getMethod()) {

            $fields = $request->request->all();
            $block = $this->getBlockService()->updateBlock($block['id'], $fields);
            $user = $this->getCurrentUser();
            $html = $this->renderView('TopxiaAdminBundle:Operation:block-tr.html.twig', array(
                'block' => $block, 'latestUpdateUser'=>$user
            ));
            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        return $this->render('TopxiaAdminBundle:Operation:block-modal.html.twig', array(
            'editBlock' => $block
        ));
    }

    public function checkBlockCodeForCreateAction(Request $request)
    {
        $code = $request->query->get('value');
        $blockByCode = $this->getBlockService()->getBlockByCode($code);
        if (empty($blockByCode)) {
            return $this->createJsonResponse(array('success' => true, 'message' => '此编码可以使用'));
        }
        return $this->createJsonResponse(array('success' => false, 'message' => '此编码已存在,不允许使用'));
    }

    public function checkBlockCodeForEditAction(Request $request, $id)
    {
        $code = $request->query->get('value');
        $blockByCode = $this->getBlockService()->getBlockByCode($code);
        if(empty($blockByCode)){
            return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
        } elseif ($id == $blockByCode['id']){
            return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
        } elseif ($id != $blockByCode['id']){
            return $this->createJsonResponse(array('success' => false, 'message' => '不允许设置为已存在的其他编码值'));
        }
    }

    public function contentIndexAction(Request $request)
    {
        $conditions = array_filter($request->query->all());

        $paginator = new Paginator(
            $request,
            $this->getContentService()->searchContentCount($conditions),
            20
        );

        $contents = $this->getContentService()->searchContents(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($contents, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $categoryIds = ArrayToolkit::column($contents, 'categoryId');
        $categories = $this->getCategoryService()->findCategoriesByIds($categoryIds);

        return $this->render('TopxiaAdminBundle:Operation:content.index.html.twig',array(
            'contents' => $contents,
            'users' => $users,
            'categories' => $categories,
            'paginator' => $paginator,
        ));
    }

    public function blockDeleteAction(Request $request, $id)
    {
        try {
            $this->getBlockService()->deleteBlock($id);
            return $this->createJsonResponse(array('status' => 'ok'));
        } catch (ServiceException $e) {
            return $this->createJsonResponse(array('status' => 'error'));
        }
    }

    private function filterSort($sort)
    {
        switch ($sort) {
            case 'byPostNum':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('postNum','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byStick':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('createdTime','DESC'),
                );
                break;
            case 'byCreatedTime':
                $orderBys=array(
                    array('createdTime','DESC'),
                );
                break;
            case 'byLastPostTime':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('lastPostTime','DESC'),
                );
                break;
            case 'byPostNum':
                $orderBys=array(
                    array('isStick','DESC'),
                    array('postNum','DESC'),
                );
                break;
            default:
                throw $this->createServiceException('参数sort不正确。');
        }
        return $orderBys;
    }

     private function getArticleService()
    {
        return $this->getServiceKernel()->createService('Article.ArticleService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
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

    protected function getGroupService()
    {
        return $this->getServiceKernel()->createService('Group.GroupService');
    }

     protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Group.ThreadService');
    }

    protected function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }

    private function getContentService()
    {
        return $this->getServiceKernel()->createService('Content.ContentService');
    }

}