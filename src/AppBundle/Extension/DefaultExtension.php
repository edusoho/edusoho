<?php
namespace AppBundle\Extension;

use Pimple\Container;
use Biz\Question\Type\Fill;
use Biz\Question\Type\Essay;
use Biz\Question\Type\Choice;
use Biz\Question\Type\Material;
use Biz\Question\Type\Determine;
use Biz\Question\Type\SingleChoice;
use Pimple\ServiceProviderInterface;
use Biz\Question\Type\UncertainChoice;

class DefaultExtension extends Extension implements ServiceProviderInterface
{
    public function getQuestionTypes()
    {
        return array(
            'choice'           => array(
                'name'      => '多选题',
                'actions'   => array(
                    'create' => 'WebBundle:ChoiceQuestion:create',
                    'edit'   => 'WebBundle:ChoiceQuestion:edit',
                    'show'   => 'WebBundle:ChoiceQuestion:show'
                ),
                'templates' => array(
                    'do' => 'WebBundle:ChoiceQuestion:do.html.twig'
                )
            ),
            'single_choice'    => array(
                'name'      => '单选题',
                'actions'   => array(
                    'create' => 'WebBundle:SingleChoiceQuestion:create',
                    'edit'   => 'WebBundle:SingleChoiceQuestion:edit',
                    'show'   => 'WebBundle:SingleChoiceQuestion:show'
                ),
                'templates' => array(
                    'do' => 'WebBundle:SingleChoiceQuestion:do.html.twig'
                )
            ),
            'uncertain_choice' => array(
                'name'      => '不定项选择题',
                'actions'   => array(
                    'create' => 'WebBundle:UncertainChoiceQuesiton:create',
                    'edit'   => 'WebBundle:UncertainChoiceQuesiton:edit',
                    'show'   => 'WebBundle:UncertainChoiceQuesiton:show'
                ),
                'templates' => array(
                    'do' => 'WebBundle:UncertainChoiceQuesiton:do.html.twig'
                )
            ),
            'determine'        => array(
                'name'      => '判断题',
                'actions'   => array(
                    'create' => 'WebBundle:DetermineQuestion:create',
                    'edit'   => 'WebBundle:DetermineQuestion:edit',
                    'show'   => 'WebBundle:DetermineQuestion:show'
                ),
                'templates' => array(
                    'do' => 'WebBundle:DetermineQuestion:do.html.twig'
                )
            ),
            'essay'            => array(
                'name'      => '判断题',
                'actions'   => array(
                    'create' => 'WebBundle:EssayQuestion:create',
                    'edit'   => 'WebBundle:EssayQuestion:edit',
                    'show'   => 'WebBundle:EssayQuestion:show'
                ),
                'templates' => array(
                    'do' => 'WebBundle:EssayQuestion:do.html.twig'
                )
            ),
            'fill'             => array(
                'name'      => '填空题',
                'actions'   => array(
                    'create' => 'WebBundle:FillQuestion:create',
                    'edit'   => 'WebBundle:FillQuestion:edit',
                    'show'   => 'WebBundle:FillQuestion:show'
                ),
                'templates' => array(
                    'do' => 'WebBundle:FillQuestion:do.html.twig'
                )
            ),
            'material'         => array(
                'name'      => '材料题',
                'actions'   => array(
                    'create' => 'WebBundle:MaterialQuestion:create',
                    'edit'   => 'WebBundle:MaterialQuestion:edit',
                    'show'   => 'WebBundle:MaterialQuestion:show'
                ),
                'templates' => array(
                    'do' => 'WebBundle:MaterialQuestion:do.html.twig'
                )
            )
        );
    }

    public function register(Container $container)
    {
        $container['question_type.choice'] = function () {
            return new Choice();
        };
        $container['question_type.single_choice'] = function () {
            return new SingleChoice();
        };
        $container['question_type.uncertain_choice'] = function () {
            return new UncertainChoice();
        };
        $container['question_type.determine'] = function () {
            return new Determine();
        };
        $container['question_type.essay'] = function () {
            return new Essay();
        };
        $container['question_type.fill'] = function () {
            return new Fill();
        };
        $container['question_type.material'] = function () {
            return new Material();
        };

    }
}
