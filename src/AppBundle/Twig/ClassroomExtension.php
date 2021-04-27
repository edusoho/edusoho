<?php

namespace AppBundle\Twig;

use Biz\Thread\Service\ThreadService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ClassroomExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('get_classroom_thread', [$this, 'getClassroomThread']),
        ];
    }

    public function getClassroomThread($threadId)
    {
        return $this->getThreadService()->getThread($threadId);
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Thread:ThreadService');
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
