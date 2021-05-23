<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\BlockToolkit;
use AppBundle\Common\Exception\AbstractException;
use AppBundle\Common\Exception\FileToolkitException;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Common\StringToolkit;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\Content\Service\BlockService;
use Biz\System\Service\SettingService;
use Biz\Theme\Service\ThemeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends BaseController
{
    public function indexAction(Request $request, $category = '')
    {
        $user = $this->getUser();

        list($condation, $sort) = $this->dealQueryFields($category);

        $paginator = new Paginator(
            $request,
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

        return $this->render('admin-v2/operating/block/index.html.twig', [
            'blockTemplates' => $blockTemplates,
            'users' => $users,
            'latestHistories' => $latestHistories,
            'paginator' => $paginator,
            'type' => $category,
        ]);
    }

    public function blockMatchAction(Request $request, $type)
    {
        list($conditions, $sort) = $this->dealQueryFields($type);
        $conditions['title'] = $request->query->get('q');

        $blocks = $this->getBlockService()->searchBlockTemplates($conditions, ['updateTime' => 'DESC'], 0, 10);
        foreach ($blocks as &$block) {
            $block['gotoUrl'] = $this->generateUrl('admin_v2_block_visual_edit', ['blockTemplateId' => $block['id'], 'type' => $type]);
        }

        return $this->createJsonResponse($blocks);
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $block = $this->getBlockService()->createBlock($request->request->all());
            $user = $this->getUser();
            $html = $this->renderView('admin-v2/operating/block/list-tr.html.twig', ['blockTemplate' => $block, 'latestUpdateUser' => $user]);

            return $this->createJsonResponse(['status' => 'ok', 'html' => $html]);
        }

        $editBlock = [
            'id' => 0,
            'title' => '',
            'code' => '',
            'mode' => 'html',
            'template' => '',
        ];

        return $this->render('admin-v2/operating/block/block-modal.html.twig', [
            'editBlock' => $editBlock,
        ]);
    }

    public function editAction(Request $request, $blockTemplateId)
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
            $html = $this->renderView('admin-v2/operating/block/list-tr.html.twig', [
                'blockTemplate' => $block,
                'latestUpdateUser' => $latestUpdateUser,
                'latestHistory' => $latestBlockHistory,
            ]);

            return $this->createJsonResponse(['status' => 'ok', 'html' => $html]);
        }

        $block = $this->getBlockService()->getBlockByTemplateIdAndOrgId($blockTemplateId, $user['orgId']);

        return $this->render('admin-v2/operating/block/block-update-modal.html.twig', [
            'block' => $block,
        ]);
    }

    public function blockTipAction(Request $request)
    {
        return $this->render('admin-v2/operating/block/block-tip-modal.html.twig');
    }

    public function visualEditAction(Request $request, $blockTemplateId, $type)
    {
        $user = $this->getUser();
        if ('POST' == $request->getMethod()) {
            $condation = $request->request->all();
            if(isset($condation['data']['honorText'][0]['value']) && !empty($condation['data']['honorText'][0]['value'])){
                $themeConfig = $this->getThemeService()->getCurrentThemeConfig();
                foreach ($themeConfig['confirmConfig']['blocks']['left'] as  &$value){
                    if($value['code']=='four-ads'){
                        $value['code']=$condation['data']['honorText'][0]['value'];
                    }
                }
                $themeConfig['confirmConfig']['blocks']['left'][9]['title']=$condation['data']['honorText'][0]['value'];
                $this->getThemeService()->editThemeConfig($themeConfig['name'],$themeConfig);
            }
            $block['data'] = $condation['data'];
            $block['templateName'] = $condation['templateName'];
            $html = BlockToolkit::render($block, $this->container);
            $fields = [
                'data' => $block['data'],
                'content' => $html,
                'userId' => $user['id'],
                'blockTemplateId' => $condation['blockTemplateId'],
                'orgId' => $user['orgId'],
                'code' => $condation['code'],
                'mode' => $condation['mode'],
            ];

            if (empty($condation['blockId'])) {
                $block = $this->getBlockService()->createBlock($fields);
            } else {
                $block = $this->getBlockService()->updateBlock($condation['blockId'], $fields);
            }

            $this->setFlashMessage('success', 'site.save.success');
        }
        $block = $this->getBlockService()->getBlockByTemplateIdAndOrgId($blockTemplateId, $user['orgId']);
        if ('imgOrVideolink' == $block['meta']['items']['ad']['type']) {
            return $this->render('admin-v2/operating/block/block-visual-certificate-edit.html.twig', [
                'block' => $block,
                'action' => 'edit',
                'type' => $type,
            ]);
        }else if('imgcertificatelink' ==$block['meta']['items']['img']['type']){
            return $this->render('admin-v2/operating/block/block-visual-imgcertificatelink-edit.html.twig', [
                'block' => $block,
                'action' => 'edit',
                'type' => $type,
            ]);
        } else {
            return $this->render('admin-v2/operating/block/block-visual-edit.html.twig', [
                'block' => $block,
                'action' => 'edit',
                'type' => $type,
            ]);
        }
    }

    public function editBlockTemplateAction(Request $request, $blockTemplateId)
    {
        $block = $this->getBlockService()->getBlockTemplate($blockTemplateId);

        if ('POST' == $request->getMethod()) {
            $fields = $request->request->all();
            $block = $this->getBlockService()->updateBlockTemplate($block['id'], $fields);
            $user = $this->getUser();
            $html = $this->renderView('admin-v2/operating/block/list-tr.html.twig', [
                'blockTemplate' => $block, 'latestUpdateUser' => $user,
            ]);

            return $this->createJsonResponse(['status' => 'ok', 'html' => $html]);
        }

        return $this->render('admin-v2/operating/block/block-modal.html.twig', [
            'editBlock' => $block,
        ]);
    }

    public function deleteAction(Request $request, $id)
    {
        try {
            $this->getBlockService()->deleteBlockTemplate($id);

            return $this->createJsonResponse(['status' => 'ok']);
        } catch (AbstractException $e) {
            return $this->createJsonResponse(['status' => 'error']);
        }
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

    public function checkBlockCodeForCreateAction(Request $request)
    {
        $code = $request->query->get('value');
        $blockTemplateByCode = $this->getBlockService()->getBlockTemplateByCode($code);
        if (empty($blockTemplateByCode)) {
            return $this->createJsonResponse(['success' => true, 'message' => '此编码可以使用']);
        }

        return $this->createJsonResponse(['success' => false, 'message' => '此编码已存在,不允许使用']);
    }

    public function visualHistoryAction(Request $request, $blockTemplateId, $type)
    {
        $user = $this->getUser();

        $block = $this->getBlockService()->getBlockByTemplateIdAndOrgId($blockTemplateId, $user['orgId']);
        $paginator = new Paginator(
            $request,
            null,
            5
        );
        $blockHistorys = [];
        $historyUsers = [];

        if (!empty($block)) {
            $paginator = new Paginator(
                $request,
                $this->getBlockService()->findBlockHistoryCountByBlockId($block['blockId']),
                20
            );

            $blockHistorys = $this->getBlockService()->findBlockHistorysByBlockId(
                $block['blockId'],
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount());

            $historyUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($blockHistorys, 'userId'));
        }

        return $this->render('admin-v2/operating/block/block-visual-history.html.twig', [
            'block' => $block,
            'paginator' => $paginator,
            'blockHistorys' => $blockHistorys,
            'historyUsers' => $historyUsers,
            'type' => $type,
        ]);
    }

    public function checkBlockTemplateCodeForEditAction(Request $request, $id)
    {
        $code = $request->query->get('value');
        $blockTemplateByCode = $this->getBlockService()->getBlockTemplateByCode($code);
        if (empty($blockTemplateByCode) || $id == $blockTemplateByCode['id']) {
            return $this->createJsonResponse(['success' => true, 'message' => 'ok']);
        } elseif ($id != $blockTemplateByCode['id']) {
            return $this->createJsonResponse(['success' => false, 'message' => '不允许设置为已存在的其他编码值']);
        }
    }

    public function recoveryAction(Request $request, $blockTemplateId, $historyId, $type)
    {
        $history = $this->getBlockService()->getBlockHistory($historyId);
        $user = $this->getUser();
        $block = $this->getBlockService()->getBlockByTemplateIdAndOrgId($blockTemplateId, $user['orgId']);
        $this->getBlockService()->recovery($block['blockId'], $history);
        $this->setFlashMessage('success', 'site.reset.success');

        return $this->redirect($this->generateUrl('admin_v2_block_visual_edit_history', ['blockTemplateId' => $blockTemplateId, 'type' => $type]));
    }

    public function uploadAction(Request $request, $blockId)
    {
        $response = [];
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

            $response = [
                'url' => $url,
            ];
        }

        return $this->createJsonResponse($response);
    }

    public function picPreviewAction(Request $request, $blockId)
    {
        $url = $request->query->get('url', '');

        return $this->render('admin-v2/operating/block/picture-preview-modal.html.twig', [
            'url' => $url,
        ]);
    }

    public function previewAction(Request $request, $id)
    {
        $blockHistory = $this->getBlockService()->getBlockHistory($id);

        return $this->render('admin-v2/operating/block/blockhistory-preview.html.twig', [
            'blockHistory' => $blockHistory,
        ]);
    }

    protected function dealQueryFields($category)
    {
        $sort = [];
        $condation = [];
        if ('lastest' == $category) {
            $sort = ['updateTime' => 'DESC'];
        } elseif ('all' != $category) {
            if ('theme' == $category) {
                $theme = $this->getSettingService()->get('theme', []);
                $category = $theme['uri'];
            }
            $condation['category'] = $category;
        }

        return [$condation, $sort];
    }

    /**
     * @return BlockService
     */
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


    /**
     * @return ThemeService
     */
    protected function getThemeService()
    {
        return $this->createService('Theme:ThemeService');
    }
}
