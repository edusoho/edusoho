<?php
namespace AppBundle\Extension;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

class DefaultExtension extends Extension implements ServiceProviderInterface
{
    public function getQuestionTypes()
    {
        return array (
            'single_choice' => array(
                'name' => '单选题',
                'actions' => array(
                    'create' => '',
                    'edit' => '',
                    'show' => '',
                ),
                'templates' => array(
                ),
            ),
        );
    }

    public function register(Container $container)
    {
        $container['question_type.single_choice'] = function() {
            // return new SingleChoice
        };

    }
}