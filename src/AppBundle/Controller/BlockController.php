<?php
namespace AppBundle\Controller;

use Biz\Content\Service\BlockService;
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

        $templateName = $this->getBlockService()->getFullBlockTemplateName($block['category'], $block['templateName'], false);
        return $this->render($templateName, array(
            'block' => $block
        ));
    }

    /**
     * @return BlockService
     */
    protected function getBlockService()
    {
        return $this->getBiz()->service('Content:BlockService');
    }
}