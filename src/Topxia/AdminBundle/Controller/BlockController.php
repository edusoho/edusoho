<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceException;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;


class BlockController extends BaseController
{
    public function indexAction(Request $request)
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

        return $this->render('TopxiaAdminBundle:Block:index.html.twig', array(
            'blocks'=>$findedBlocks,
            'latestUpdateUser'=>$latestUpdateUser,
            'paginator' => $paginator
        ));
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

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }

}