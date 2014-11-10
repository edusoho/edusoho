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
                    'label' => 'form.baseinfo.acctname',
                )
            )
            ->add(
                'describe',
                'text',
                array(
                    'label' => 'form.baseinfo.describe',
                )
            );
    }

    public function getName()
    {
        return 'wechat_acctsetup_baseinfo';
    }
} 