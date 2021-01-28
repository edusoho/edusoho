<?php

namespace AppBundle\Twig;

use Codeages\Biz\Framework\Context\Biz;
use AppBundle\Common\BlockToolkit;
use Symfony\Component\HttpFoundation\RequestStack;

class BlockExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('block_show', array($this, 'showBlock'), array('is_safe' => array('html'))),
        );
    }

    public function showBlock($code)
    {
        $block = $this->biz->service('Content:BlockService')->getBlockByCode($code);
        if (empty($block)) {
            return '';
        }

        $env = $this->container->getParameter('kernel.environment');

        if ($env == 'prod') {
            $content = isset($block['content']) ? $block['content'] : '';
        } else {
            $content = BlockToolkit::render($block, $this->container);
        }

        $content = $this->container->get('web.twig.extension')->cdn($content);

        return $content;
    }

    public function getName()
    {
        return 'topxia_block_twig';
    }
}
