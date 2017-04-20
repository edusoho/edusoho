<?php

namespace AppBundle\Twig;

class QuestionTypeExtension extends \Twig_Extension
{
    protected $biz;
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
            new \Twig_SimpleFunction('getQuestionTypes', array($this, 'getQuestionTypes')),
            new \Twig_SimpleFunction('getQuestionTypeTemplate', array($this, 'getQuestionTypeTemplate')),
        );
    }

    public function getQuestionTypes()
    {
        $questionExtension = $this->container->get('extension.manager')->getQuestionTypes();

        $types = array();
        $questionTypes = array_walk($questionExtension, function ($value, $type) use (&$types) {
            $types[$type] = $value['name'];
        });

        return $types;
    }

    public function getQuestionTypeTemplate($type, $showAction)
    {
        $questionExtension = $this->container->get('extension.manager')->getQuestionTypes();

        if (empty($questionExtension[$type]) || empty($questionExtension[$type]['templates'][$showAction])) {
            return '';
        }

        return $questionExtension[$type]['templates'][$showAction];
    }

    public function getName()
    {
        return 'web_question_type_twig';
    }
}
