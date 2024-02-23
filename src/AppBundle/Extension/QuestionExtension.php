<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class QuestionExtension extends Extension implements ServiceProviderInterface
{
    public function getQuestionTypes()
    {
        return [
            'single_choice' => [
                'name' => 'question.type.single_choice',
                'templates' => [
                    'do' => 'question/single-choice-do.html.twig',
                    'analysis' => 'question/analysis/single-choice.html.twig',
                ],
                'hasMissScore' => 0,
                'seqNum' => 1,
            ],
            'choice' => [
                'name' => 'question.type.multiple_choice',
                'templates' => [
                    'do' => 'question/choice-do.html.twig',
                    'analysis' => 'question/analysis/choice.html.twig',
                ],
                'hasMissScore' => 1,
                'seqNum' => 2,
            ],
            'essay' => [
                'name' => 'question.type.essay',
                'templates' => [
                    'do' => 'question/essay-do.html.twig',
                    'analysis' => 'question/analysis/essay.html.twig',
                ],
                'hasMissScore' => 0,
                'seqNum' => 3,
            ],
            'uncertain_choice' => [
                'name' => 'question.type.uncertain_choice',
                'templates' => [
                    'do' => 'question/uncertain-choice-do.html.twig',
                    'analysis' => 'question/analysis/uncertain_choice.html.twig',
                ],
                'hasMissScore' => 1,
                'seqNum' => 4,
            ],
            'determine' => [
                'name' => 'question.type.determine',
                'templates' => [
                    'do' => 'question/determine-do.html.twig',
                    'analysis' => 'question/analysis/determine.html.twig',
                ],
                'hasMissScore' => 0,
                'seqNum' => 5,
            ],
            'fill' => [
                'name' => 'question.type.fill',
                'templates' => [
                    'do' => 'question/fill-do.html.twig',
                    'analysis' => 'question/analysis/fill.html.twig',
                ],
                'hasMissScore' => 0,
                'seqNum' => 6,
            ],
            'material' => [
                'name' => 'question.type.material',
                'templates' => [
                    'do' => 'question/material-do.html.twig',
                    'analysis' => 'question/analysis/material.html.twig',
                ],
                'hasMissScore' => 0,
                'seqNum' => 7,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
    }
}
