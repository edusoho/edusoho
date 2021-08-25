<?php

namespace AppBundle\Twig;

use Biz\SCRM\Service\SCRMService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MultiClassExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_scrm_bind', [$this, 'isScrmBind']),
        ];
    }

    public function isScrmBind()
    {
        return $this->getSCRMService()->isScrmBind();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'multi_class';
    }

    /**
     * @return SCRMService
     */
    protected function getSCRMService()
    {
        return $this->biz->service('SCRM:SCRMService');
    }
}
