<?php

namespace AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class QuestionTypeExtension extends \Twig_Extension
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
        return [];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('getQuestionTypes', [$this, 'getQuestionTypes']),
            new \Twig_SimpleFunction('getQuestionTypeTemplate', [$this, 'getQuestionTypeTemplate']),
            new \Twig_SimpleFunction('getQuestionTypeSeq', [$this, 'getQuestionTypeSeq']),
            new \Twig_SimpleFunction('sortQuestionTypes', [$this, 'sortQuestionTypes']),
        ];
    }

    public function getQuestionTypes()
    {
        $questionExtension = $this->container->get('extension.manager')->getQuestionTypes();
        $container = $this->container;

        $types = [];
        array_walk($questionExtension, function ($value, $type) use (&$types, $container) {
            $types[$type] = $container->get('translator')->trans($value['name']);
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

    public function getQuestionTypeSeq()
    {
        $questionExtension = $this->container->get('extension.manager')->getQuestionTypes();

        $typeSeq = [];
        array_walk($questionExtension, function ($value, $type) use (&$typeSeq) {
            $typeSeq[$type] = $value['seqNum'];
        });

        return $typeSeq;
    }

    public function sortQuestionTypes($types, $sections = [])
    {
        if (empty($sections)) {
            return $types;
        }

        $newTypes = [];
        $questionExtension = $this->container->get('extension.manager')->getQuestionTypes();

        foreach ($sections as $section) {
            $newTypes[$section['type']] = $section['name'];
        }

        $existTypes = array_keys($newTypes);
        $container = $this->container;
        array_walk($questionExtension, function ($value, $type) use (&$newTypes, $container, $existTypes) {
            if (!in_array($type, $existTypes)) {
                $newTypes[$type] = $container->get('translator')->trans($value['name']);
            }
        });

        return $newTypes;
    }

    public function getName()
    {
        return 'web_question_type_twig';
    }
}
