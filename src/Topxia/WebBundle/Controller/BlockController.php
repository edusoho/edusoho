<?php
namespace Topxia\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class BlockController extends BaseController
{
    public function renderAction(Request $request, $code)
    {
        $block = $this->getBlockService()->getBlockByCode($code);
        $templateName = $this->getFullBlockTemplateName($block['category'], $block['templateName'], false);
        return $this->render($templateName, array(
            'block' => $block
        ));
    }

    private function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }
}