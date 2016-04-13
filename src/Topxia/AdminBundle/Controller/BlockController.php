<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Common\ServiceException;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Common\BlockToolkit;
use Topxia\Common\StringToolkit;

class BlockController extends BaseController
{
    public function indexAction(Request $request, $category='')
    {

        list($condation, $sort)= $this->dealQueryFields($category);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getBlockService()->searchBlockCount($condation),
            20
        );
        $findedBlocks = $this->getBlockService()->searchBlocks($condation, $sort, $paginator->getOffsetCount(),
            $paginator->getPerPageCount());
        
        $blockIds = ArrayToolkit::column($findedBlocks, 'id');
        $latestHistories = $this->getBlockService()->getLatestBlockHistoriesByBlockIds($blockIds);
        $userIds = ArrayToolkit::column($latestHistories, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('TopxiaAdminBundle:Block:index.html.twig', array(
            'blocks'=>$findedBlocks,
            'users'=>$users,
            'latestHistories' => $latestHistories,
            'paginator' => $paginator,
            'type' => $category
        ));
    }
    protected function dealQueryFields($category)
    {
        $sort = array();
        $condation = array();
        if($category =='lastest'){
            $sort = array('updateTime', 'DESC');
        }elseif($category != 'all'){
          if($category == 'theme'){
            $theme = $this->getSettingService()->get('theme', array());
            $category = $theme['uri'];
          }
           $condation['category'] =  $category;
        }
        return array($condation,  $sort);
    }

    public function blockMatchAction(Request $request){
        $likeString = $request->query->get('q');
        $blocks = $this->getBlockService()->searchBlocks(array('title'=>$likeString), array('updateTime', 'DESC'), 0, 10);
        return $this->createJsonResponse($blocks);
    }
    public function previewAction(Request $request, $id)
    {
        $blockHistory = $this->getBlockService()->getBlockHistory($id);
        return $this->render('TopxiaAdminBundle:Block:blockhistory-preview.html.twig', array(
            'blockHistory'=>$blockHistory
        ));
    }

    public function updateAction(Request $request, $block)
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
                }
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
            $html = $this->renderView('TopxiaAdminBundle:Block:list-tr.html.twig', array(
                'block' => $block, 'latestUpdateUser'=>$latestUpdateUser
            ));
            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));          
        }

        return $this->render('TopxiaAdminBundle:Block:block-update-modal.html.twig', array(
            'block' => $block,
            'blockHistorys' => $blockHistorys,
            'historyUsers' => $historyUsers,
            'paginator' => $paginator,
            'templateItems' => $templateItems,
            'templateData' => $templateData
        ));
    }

    public function editAction(Request $request, $block)
    {
        $block = $this->getBlockService()->getBlock($block);

        if ('POST' == $request->getMethod()) {

            $fields = $request->request->all();
            $block = $this->getBlockService()->updateBlock($block['id'], $fields);
            $user = $this->getCurrentUser();
            $html = $this->renderView('TopxiaAdminBundle:Block:list-tr.html.twig', array(
                'block' => $block, 'latestUpdateUser'=>$user
            ));
            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        return $this->render('TopxiaAdminBundle:Block:block-modal.html.twig', array(
            'editBlock' => $block
        ));
    }

    public function visualEditAction(Request $request, $blockId)
    {
        $block = $this->getBlockService()->getBlock($blockId);

        if ('POST' == $request->getMethod()) {
            $data = $request->request->get('data');
            $block['data'] = $data;
            $html = BlockToolkit::render($block, $this->container);
            
            $block = $this->getBlockService()->updateBlock($blockId, array(
                'data' => $data,
                'content' => $html
            ));
            $this->setFlashMessage('success', '保存成功!');
        }

        return $this->render('TopxiaAdminBundle:Block:block-visual-edit.html.twig', array(
            'block' => $block,
            'action' => 'edit',
        ));
    }

    public function dataViewAction(Request $request, $blockId)
    {
        $block = $this->getBlockService()->getBlock($blockId);
        unset($block['meta']['default']);
        foreach ($block['meta']['items'] as $key => &$item) {
            $item['default'] = $block['data'][$key];
        }
       
        return new Response('<pre>' . StringToolkit::jsonPettry(json_encode($block['meta'], JSON_UNESCAPED_UNICODE)) . '</pre>');
    }

    public function visualHistoryAction(Request $request, $blockId)
    {
        $block = $this->getBlockService()->getBlock($blockId);
        $paginator = new Paginator(
            $this->get('request'),
            $this->getBlockService()->findBlockHistoryCountByBlockId($block['id']),
            20
        );

        $blockHistorys = $this->getBlockService()->findBlockHistorysByBlockId(
            $block['id'], 
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount());

        $historyUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($blockHistorys, 'userId'));
        return $this->render("TopxiaAdminBundle:Block:block-visual-history.html.twig", array(
            'block' => $block,
            'paginator' => $paginator,
            'blockHistorys' => $blockHistorys,
            'historyUsers' => $historyUsers
        ));
    }

    public function createAction(Request $request)
    {
        
        if ('POST' == $request->getMethod()) {
            $block = $this->getBlockService()->createBlock($request->request->all());
            $user = $this->getCurrentUser();
            $html = $this->renderView('TopxiaAdminBundle:Block:list-tr.html.twig', array('block' => $block,'latestUpdateUser'=>$user));
            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        $editBlock = array(
            'id' => 0,
            'title' => '',
            'code' => '',
            'mode' => 'html',
            'template' => ''
        );

        return $this->render('TopxiaAdminBundle:Block:block-modal.html.twig', array(
            'editBlock' => $editBlock
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        try {
            $this->getBlockService()->deleteBlock($id);
            return $this->createJsonResponse(array('status' => 'ok'));
        } catch (ServiceException $e) {
            return $this->createJsonResponse(array('status' => 'error'));
        }
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

    public function uploadAction(Request $request, $blockId)
    {
        $response = array();
        if ($request->getMethod() == 'POST') {
            $file = $request->files->get('file');
            if (!FileToolkit::isImageFile($file)) {
                throw $this->createAccessDeniedException('图片格式不正确！');
            }

            $filename = 'block_picture_' . time() . '.' . $file->getClientOriginalExtension();

            $directory = "{$this->container->getParameter('topxia.upload.public_directory')}/system";
            $file = $file->move($directory, $filename);

            $block = $this->getBlockService()->getBlock($blockId);

            $url = "{$this->container->getParameter('topxia.upload.public_url_path')}/system/{$filename}";

            $response = array(
                'url' => $url,
            );
        }
        return $this->createJsonResponse($response);
    }

    public function picPreviewAction(Request $request, $blockId)
    {
        $url = $request->query->get('url', '');
        return $this->render('TopxiaAdminBundle:Block:picture-preview-modal.html.twig', array(
            'url' => $url
        ));
    }

    public function recoveryAction(Request $request, $blockId, $historyId)
    {
        $history = $this->getBlockService()->getBlockHistory($historyId);
        $this->getBlockService()->recovery($blockId, $history);
        $this->setFlashMessage('success', '恢复成功!');
        return $this->redirect($this->generateUrl('admin_block_visual_edit_history', array('blockId' => $blockId)));
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

}