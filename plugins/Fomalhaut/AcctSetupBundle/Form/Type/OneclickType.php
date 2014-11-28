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
                    'label' => '微信公众号用户名',//form.oneclick.wxusername
                )
            )
            ->add(
                'wxpassword',
                'password',
                array(
                    'label' => '微信公众号密码',//form.oneclick.wxpassword
                )
            )
            ->add(
                'imgcode',
                'text',
                array(
                    'label' => '验证码',//form.oneclick.imgcode
                )
            );
    }

    public function getName()
    {
        return 'wechat_acctsetup_oneclick';
    }
} 