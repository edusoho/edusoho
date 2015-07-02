<?php
namespace Topxia\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends BaseController
{
    public function renderAction(Request $request, $code)
    {
        $block = $this->getBlockService()->getBlockByCode($code);
        if (empty($block) || empty($block['templateName'])) {
            return new Response('');
        }
        $templateName = $this->getFullBlockTemplateName($block['category'], $block['templateName'], false);
        return $this->render($templateName, array(
            'block' => $block
        ));
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }
}