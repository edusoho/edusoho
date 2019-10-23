<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Content\Service\BlockService;
use Biz\System\Service\SettingService;
use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

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
}
