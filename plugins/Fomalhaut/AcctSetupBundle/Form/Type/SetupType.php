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
                    'label' => '公众号名称',//form.setup.name
                )
            )
            ->add(
                'account',
                'text',
                array(
                    'label' => '公众号帐号',//form.setup.account
                    'required' => false,
                )
            )
            ->add(
                'level',
                'choice',
                array(
                    'label' => '级别',//form.setup.level
                    'expanded' => true,
                    'empty_value' => false,
                    'required' => false,
                    'choices' => array(
                        'level_ordinsub' => '普通订阅号',//form.setup.level.ordinary_subscription
                        'level_authsub_ordinserv' => '认证订阅号/普通服务号',//form.setup.level.auth_sub_or_ordin_serv
                        'level_authserv' => '认证服务号',//form.setup.level.auth_sub_or_ordin_serv
                    ),
                )
            )
            ->add(
                'key',
                'text',
                array(
                    'label' => 'AppId',//form.setup.key
                    'required' => false,
                )
            )
            ->add(
                'secret',
                'text',
                array(
                    'label' => 'AppSecret',//form.setup.secret
                    'required' => false,
                )
            )
            ->add(
                'interfaceaddr',
                'text',
                array(
                    'label' => '接口地址',//form.setup.interface_address
                    'required' => false,
                )
            )
            ->add(
                'token',
                'text',
                array(
                    'label' => 'Token',//form.setup.token
                    'required' => false,
                )
            )
            ->add(
                'encodingaeskey',
                'text',
                array(
                    'label' => 'EncodingAESKey',//form.setup.encodingaeskey
                    'required' => false,
                )
            )
            ->add(
                'qrcode',
                'file',
                array(
                    'label' => '二维码',//form.setup.qrcode
                    'required' => false,
                )
            )
            ->add(
                'headimg',
                'file',
                array(
                    'label' => '头像',//form.setup.headimg
                    'required' => false,
                )
            );
    }

    public function getName()
    {
        return 'wechat_acctsetup_setup';
    }
}