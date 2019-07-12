<?php

namespace Biz\Mail;

use AppBundle\Extension\Extension;
use Biz\Mail\Template\EffectEmailResetPasswordTemplate;
use Biz\Mail\Template\EmailImportUserEmailTemplate;
use Biz\Mail\Template\EmailRegistrationTemplate;
use Biz\Mail\Template\EmailResetEmailTemplate;
use Biz\Mail\Template\EmailResetPasswordTemplate;
use Biz\Mail\Template\EmailSystemSelfTestTemplate;
use Biz\Mail\Template\EmailVerifyEmailTemplate;
use Biz\Mail\Template\EmptyTemplate;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MailServiceProvider extends Extension implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $biz)
    {
        $this->registerEmailTemplate($biz);

        $biz['normal_mail'] = function ($biz) {
            return function ($mailOptions) {
                return new NormalMail($mailOptions);
            };
        };

        $biz['cloud_mail'] = function () {
            return function ($mailOptions) {
                return new CloudMail($mailOptions);
            };
        };

        $biz['mail_factory'] = $biz->factory(function ($biz) {
            $cloudConfig = $biz->service('System:SettingService')->get('cloud_email_crm', array());

            return function ($mailOptions) use ($cloudConfig, $biz) {
                /*
                 * @var Mail $mail
                 */
                if (isset($cloudConfig['status']) && $cloudConfig['status'] == 'enable') {
                    $mail = $biz['cloud_mail']($mailOptions);
                } else {
                    $mail = $biz['normal_mail']($mailOptions);
                }
                $mail->setBiz($biz);

                return $mail;
            };
        });
    }

    private function registerEmailTemplate(Container $biz)
    {
        $biz['email_template_paths'] = $biz->factory(function () {
            return array();
        });

        $biz['email_template_paths'] = $biz->extend('email_template_paths', function ($paths, $biz) {
            return array_merge($paths, array(__DIR__.'/Template/twig'));
        });

        $biz['email_template_parser'] = function ($biz) {
            $parser = new EmailTemplateParser();
            $parser->setBiz($biz);

            return $parser;
        };

        $biz['empty_email_template'] = function ($biz) {
            return new EmptyTemplate();
        };

        $biz['effect_email_reset_password_template'] = function ($biz) {
            $template = new EffectEmailResetPasswordTemplate();
            $template->setBiz($biz);

            return $template;
        };

        $biz['email_reset_password_template'] = function ($biz) {
            $template = new EmailResetPasswordTemplate();
            $template->setBiz($biz);

            return $template;
        };

        $biz['email_system_self_test_template'] = function ($biz) {
            $template = new EmailSystemSelfTestTemplate();
            $template->setBiz($biz);

            return $template;
        };

        $biz['email_registration_template'] = function ($biz) {
            $template = new EmailRegistrationTemplate();
            $template->setBiz($biz);

            return $template;
        };

        $biz['email_reset_email_template'] = function ($biz) {
            $template = new EmailResetEmailTemplate();
            $template->setBiz($biz);

            return $template;
        };

        $biz['email_verify_email_template'] = function ($biz) {
            $template = new EmailVerifyEmailTemplate();
            $template->setBiz($biz);

            return $template;
        };

        $biz['email_import_user_email_template'] = function ($biz) {
            $template = new EmailImportUserEmailTemplate();
            $template->setBiz($biz);

            return $template;
        };
    }
}
