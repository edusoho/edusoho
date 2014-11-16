<?php

namespace Fomalhaut\AcctSetupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OneclickType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'wxusername',
                'text',
                array(
                    'label' => 'form.oneclick.wxusername',
                )
            )
            ->add(
                'wxpassword',
                'text',
                array(
                    'label' => 'form.oneclick.wxpassword',
                )
            )
            ->add(
                'imgcode',
                'text',
                array(
                    'label' => 'form.oneclick.imgcode',
                )
            );
    }

    public function getName()
    {
        return 'wechat_acctsetup_oneclick';
    }
} 