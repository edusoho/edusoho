<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\DistributionBundle\Configurator\Form;

use Sensio\Bundle\DistributionBundle\Configurator\Step\DoctrineStep;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Doctrine Form Type.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DoctrineStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('driver', 'choice', array('choices' => DoctrineStep::getDrivers()))
            ->add('name', 'text', array('required' => false))
            ->add('host', 'text', array('required' => false))
            ->add('path', 'text', array('required' => false))
            ->add('port', 'text', array('required' => false))
            ->add('user', 'text', array('required' => false))
            ->add('password', 'repeated', array(
                'required' => false,
                'type' => 'password',
                'first_name' => 'password',
                'second_name' => 'password_again',
                'invalid_message' => 'The password fields must match.',
            ))
        ;
    }

    public function getName()
    {
        return 'distributionbundle_doctrine_step';
    }
}
