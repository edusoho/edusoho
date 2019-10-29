<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Exception\AbstractException;
use AppBundle\Common\Exception\FileToolkitException;
use Biz\System\Service\SettingService;
use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\BlockToolkit;
use AppBundle\Common\StringToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends BaseController
{
    public function indexAction(Request $request, $category = '')
    {
        $user = $this->getUser();

        list($condation, $sort) = $this->dealQueryFields($category);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getBlockService()->searchBlockTemplateCount($condation),
            20
        );
        $blockTemplates = $this->getBlockService()->searchBlockTemplates(
            $condation,
            $sort,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $blockTemplateIds = ArrayToolkit::column($blockTemplates, 'id');

        $blocks = $this->getBlockService()->getBlocksByBlockTemplateIdsAndOrgId($blockTemplateIds, $user['orgId']);
        $blockIds = ArrayToolkit::column($blocks, 'id');
        $latestHistories = $this->getBlockService()->getLatestBlockHistoriesByBlockIds($blockIds);
        $userIds = ArrayToolkit::column($latestHistories, 'userId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('admin/block/index.html.twig', array(
            'blockTemplates' => $blockTemplates,
            'users' => $users,
            'latestHistories' => $latestHistories,
            'paginator' => $paginator,
            'type' => $category,
        ));
    }

    protected function dealQueryFields($category)
    {
        $sort = array();
        $condation = array();
        if ('lastest' == $category) {
            $sort = array('updateTime' => 'DESC');
        } elseif ('all' != $category) {
            if ('theme' == $category) {
                $theme = $this->getSettingService()->get('theme', array());
                $category = $theme['uri'];
            }
            $condation['category'] = $category;
        }

        return array($condation, $sort);
    }

    public function blockMatchAction(Request $request)
    {
        $likeString = $request->query->get('q');
        $blocks = $this->getBlockService()->searchBlockTemplates(array('title' => $likeString), array('updateTime' => 'DESC'), 0, 10);
        foreach ($blocks as &$block) {
            $block['gotoUrl'] = $this->generateUrl('admin_block_visual_edit', array('blockTemplateId' => $block['id']));
        }

        return $this->createJsonResponse($blocks);
    }

    public function previewAction(Request $request, $id)
    {
        $blockHistory = $this->getBlockService()->getBlockHistory($id);

        return $this->render('admin/block/blockhistory-preview.html.twig', array(
            'blockHistory' => $blockHistory,
        ));
    }

    public function updateAction(Request $request, $blockTemplateId)
    {
        $user = $this->getUser();

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $fields['userId'] = $user['id'];
            $fields['orgId'] = $user['orgId'];
            if (empty($fields['blockId'])) {
                $block = $this->getBlockService()->createBlock($fields);
            } else {
                $block = $this->getBlockService()->updateBlock($fields['blockId'], $fields);
            }
            $latestBlockHistory = $this->getBlockService()->getLatestBlockHistory();
            $latestUpdateUser = $this->getUserService()->getUser($latestBlockHistory['userId']);
            $html = $this->renderView('admin/block/list-tr.html.twig', array(
                'blockTemplate' => $block,
                'latestUpdateUser' => $latestUpdateUser,
                'latestHistory' => $latestBlockHistory,
            ));

            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        $block = $this->getBlockService()->getBlockByTemplateIdAndOrgId($blockTemplateId, $user['orgId']);

        return $this->render('admin/block/block-update-modal.html.twig', array(
            'block' => $block,
        ));
    }

    public function blockHistoriesDataAction($blockId)
    {
        $block = $this->getBlockService()->getBlock($blockId);
        $templateData = array();
        $templateItems = array();
        $blockHistorys = array();
        $historyUsers = array();
        $paginator = new Paginator(
            $this->get('request'),
            null,
            5);
        if (!empty($block['blockId'])) {
            $paginator = new Paginator(
                $this->get('request'),
                $this->getBlockService()->findBlockHistoryCountByBlockId($block['blockId']),
                5
            );

            $blockHistorys = $this->getBlockService()->findBlockHistorysByBlockId(
                $blockId,
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount());

            foreach ($blockHistorys as &$blockHistory) {
                $blockHistory['templateData'] = json_decode($blockHistory['templateData'], true);
            }

            $historyUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($blockHistorys, 'userId'));
        }

        return $this->render('admin/block/block-history-table.html.twig', array(
            'block' => $block,
            'blockHistorys' => $blockHistorys,
            'historyUsers' => $historyUsers,
            'paginator' => $paginator,
        ));
    }

    public function editAction(Request $request, $blockTemplateId)
    {
        $block = $this->getBlockService()->getBlockTemplate($blockTemplateId);

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $block = $this->getBlockService()->updateBlockTemplate($block['id'], $fields);
            $user = $this->getUser();
            $html = $this->renderView('admin/block/list-tr.html.twig', array(
                'blockTemplate' => $block, 'latestUpdateUser' => $user,
            ));

            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        return $this->render('admin/block/block-modal.html.twig', array(
            'editBlock' => $block,
        ));
    }

    public function visualEditAction(Request $request, $blockTemplateId)
    {
        $user = $this->getUser();
        if ('POST' == $request->getMethod()) {
            $condation = $request->request->all();
            $block['data'] = $condation['data'];
            $block['templateName'] = $condation['templateName'];
            $html = BlockToolkit::render($block, $this->container);
            $fields = array(
                'data' => $block['data'],
                'content' => $html,
                'userId' => $user['id'],
                'blockTemplateId' => $condation['blockTemplateId'],
                'orgId' => $user['orgId'],
                'code' => $condation['code'],
                'mode' => $condation['mode'],
            );
            if (empty($condation['blockId'])) {
                $block = $this->getBlockService()->createBlock($fields);
            } else {
                $block = $this->getBlockService()->updateBlock($condation['blockId'], $fields);
            }

            $this->setFlashMessage('success', 'site.save.success');
        }

        $block = $this->getBlockService()->getBlockByTemplateIdAndOrgId($blockTemplateId, $user['orgId']);

        return $this->render('admin/block/block-visual-edit.html.twig', array(
            'block' => $block,
            'action' => 'edit',
        ));
    }

    public function dataViewAction(Request $request, $blockTemplateId)
    {
        $block = $this->getBlockService()->getBlockTemplate($blockTemplateId);
        unset($block['meta']['default']);
        foreach ($block['meta']['items'] as $key => &$item) {
            $item['default'] = $block['data'][$key];
        }

        return new Response('<pre>'.StringToolkit::jsonPettry(StringToolkit::jsonEncode($block['meta'])).'</pre>');
    }

    public function visualHistoryAction(Request $request, $blockTemplateId)
    {
        $user = $this->getUser();

        $block = $this->getBlockService()->getBlockByTemplateIdAndOrgId($blockTemplateId, $user['orgId']);
        $paginator = new Paginator(
            $this->get('request'),
            null,
            5
        );
        $blockHistorys = array();
        $historyUsers = array();

        if (!empty($block)) {
            $paginator = new Paginator(
                $this->get('request'),
                $this->getBlockService()->findBlockHistoryCountByBlockId($block['blockId']),
                20
            );

            $blockHistorys = $this->getBlockService()->findBlockHistorysByBlockId(
                $block['blockId'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount());

            $historyUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($blockHistorys, 'userId'));
        }

        return $this->render('admin/block/block-visual-history.html.twig', array(
            'block' => $block,
            'paginator' => $paginator,
            'blockHistorys' => $blockHistorys,
            'historyUsers' => $historyUsers,
        ));
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $block = $this->getBlockService()->createBlock($request->request->all());
            $user = $this->getUser();
            $html = $this->renderView('admin/block/list-tr.html.twig', array('blockTemplate' => $block, 'latestUpdateUser' => $user));

            return $this->createJsonResponse(array('status' => 'ok', 'html' => $html));
        }

        $editBlock = array(
            'id' => 0,
            'title' => '',
            'code' => '',
            'mode' => 'html',
            'template' => '',
        );

        return $this->render('admin/block/block-modal.html.twig', array(
            'editBlock' => $editBlock,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        try {
            $this->getBlockService()->deleteBlockTemplate($id);

            return $this->createJsonResponse(array('status' => 'ok'));
        } catch (AbstractException $e) {
            return $this->createJsonResponse(array('status' => 'error'));
        }
    }

    public function checkBlockCodeForCreateAction(Request $request)
    {
        $code = $request->query->get('value');
        $blockTemplateByCode = $this->getBlockService()->getBlockTemplateByCode($code);
        if (empty($blockTemplateByCode)) {
            return $this->createJsonResponse(array('success' => true, 'message' => '此编码可以使用'));
        }

        return $this->createJsonResponse(array('success' => false, 'message' => '此编码已存在,不允许使用'));
    }

    public function checkBlockTemplateCodeForEditAction(Request $request, $id)
    {
        $code = $request->query->get('value');
        $blockTemplateByCode = $this->getBlockService()->getBlockTemplateByCode($code);
        if (empty($blockTemplateByCode) || $id == $blockTemplateByCode['id']) {
            return $this->createJsonResponse(array('success' => true, 'message' => 'ok'));
        } elseif ($id != $blockTemplateByCode['id']) {
            return $this->createJsonResponse(array('success' => false, 'message' => '不允许设置为已存在的其他编码值'));
        }
    }

    public function uploadAction(Request $request, $blockId)
    {
        $response = array();
        if ('POST' == $request->getMethod()) {
            $file = $request->files->get('file');
            if (!FileToolkit::isImageFile($file)) {
                $this->createNewException(FileToolkitException::NOT_IMAGE());
            }

            $filename = 'block_picture_'.time().'.'.$file->getClientOriginalExtension();

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

        return $this->render('admin/block/picture-preview-modal.html.twig', array(
            'url' => $url,
        ));
    }

    public function recoveryAction(Request $request, $blockTemplateId, $historyId)
    {
        $history = $this->getBlockService()->getBlockHistory($historyId);
        $user = $this->getUser();
        $block = $this->getBlockService()->getBlockByTemplateIdAndOrgId($blockTemplateId, $user['orgId']);
        $this->getBlockService()->recovery($block['blockId'], $history);
        $this->setFlashMessage('success', 'site.reset.success');

        return $this->redirect($this->generateUrl('admin_block_visual_edit_history', array('blockTemplateId' => $blockTemplateId)));
    }

    protected function getBlockService()
    {
        return $this->createService('Content:BlockService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
