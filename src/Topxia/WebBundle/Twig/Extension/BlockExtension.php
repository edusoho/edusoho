<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Common\MenuBuilder;

class BlockExtension extends \Twig_Extension
{
    protected $container;

    public function __construct ($container)
    {
        $this->container = $container;
    }

    public function getFilters ()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            'block_show' => new \Twig_Function_Method($this, 'showBlock'),
        );
    }

    public function showBlock($code)
    {
        $block = ServiceKernel::instance()->createService('Content.BlockService')->getBlockByCode($code);
        if (empty($block)) {
            return '';
        }

        $env = $this->container->getParameter('kernel.environment');
        if ($env == 'prod') {
            return $block['content'];
        }

        $html = '';

        // 从data渲染生成html然后返回


    }

    public function getName ()
    {
        return 'topxia_block_twig';
    }

}


