<?php

namespace AppBundle\Controller\Callback\Marketing;

use AppBundle\Controller\BaseController;
use Codeages\Biz\Framework\Context\Biz;

class MarketingBase extends BaseController
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    protected function createService($alias)
    {
        $biz = $this->container->get('biz');

        return $biz->service($alias);
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }
}
