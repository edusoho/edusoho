<?php

namespace AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class QuestionExtension extends \Twig_Extension
{
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container, $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('find_question_num_group_by_', array($this, 'getQuestionTypes')),
            new \Twig_SimpleFunction('getQuestionTypeTemplate', array($this, 'getQuestionTypeTemplate')),
        );
    }

    public function getName()
    {
        return 'web_question_twig';
    }
}
