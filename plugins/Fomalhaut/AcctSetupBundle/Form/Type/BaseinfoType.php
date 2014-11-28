<?php
namespace Fomalhaut\AcctSetupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BaseinfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'acctname',
                'text',
                array(
                    'label' => '名称',//form.baseinfo.acctname
                )
            )
            ->add(
                'description',
                'textarea',
                array(
                    'label' => '描述',//form.baseinfo.description
                    'required' => false,
                )
            );
    }

    public function getName()
    {
        return 'wechat_acctsetup_baseinfo';
    }
} 