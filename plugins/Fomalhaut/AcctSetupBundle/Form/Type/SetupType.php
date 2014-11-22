<?php
namespace Fomalhaut\AcctSetupBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SetupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                'text',
                array(
                    'label' => 'form.setup.name',
                )
            )
            ->add(
                'account',
                'text',
                array(
                    'label' => 'form.setup.account',
                    'required' => false,
                )
            )
            ->add(
                'level',
                'choice',
                array(
                    'label' => 'form.setup.level',
                    'required' => false,
                    'choices' => array(
                        'level_ordinsub' => 'form.setup.level.ordinary_subscription',
                        'level_authsub_ordinserv' => 'form.setup.level.auth_sub_or_ordin_serv',
                        'level_authserv' => 'form.setup.level.auth_sub_or_ordin_serv',
                    ),
                )
            )
            ->add(
                'key',
                'text',
                array(
                    'label' => 'form.setup.key',
                    'required' => false,
                )
            )
            ->add(
                'secret',
                'text',
                array(
                    'label' => 'form.setup.secret',
                    'required' => false,
                )
            )
            ->add(
                'interfaceaddr',
                'text',
                array(
                    'label' => 'form.setup.interface_address',
                    'required' => false,
                )
            )
            ->add(
                'token',
                'text',
                array(
                    'label' => 'form.setup.token',
                    'required' => false,
                )
            )
            ->add(
                'encodingaeskey',
                'text',
                array(
                    'label' => 'form.setup.encodingaeskey',
                    'required' => false,
                )
            )
            ->add(
                'qrcode',
                'file',
                array(
                    'label' => 'form.setup.qrcode',
                    'required' => false,
                )
            )
            ->add(
                'headimg',
                'file',
                array(
                    'label' => 'form.setup.headimg',
                    'required' => false,
                )
            );
    }

    public function getName()
    {
        return 'wechat_acctsetup_setup';
    }
}