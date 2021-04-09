<?php

namespace AppBundle\Extension;

use Biz\NewComer\AuthSettingTask;
use Biz\NewComer\CloudAppliedTask;
use Biz\NewComer\CourseCreatedTask;
use Biz\NewComer\DecorationWebTask;
use Biz\NewComer\ManagementQualificationsTask;
use Biz\NewComer\PaymentAppliedTask;
use Biz\NewComer\PluginRegisterTask;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class NewcomerExtension extends Extension implements ServiceProviderInterface
{
    public function getNewcomerTasks()
    {
        return [
            'management_qualifications_task' => [
                'name' => '合规经营',
                'description' => '网校合规经营资质，把控网校关停风险',
                'url' => '/admin/v2/setting/ugc/qualification',
                'guideUrl' => 'https://www.qiqiuyu.com/goods/show/80?targetId=451',
                'status' => false,
                'class' => ManagementQualificationsTask::class,
            ],
            'cloud_applied_task' => [
                'name' => 'admin_v2.newcomer_task.cloud_applied.title',
                'description' => 'admin_v2.newcomer_task.cloud_applied.tip',
                'url' => '/admin/v2/setting/cloud/key/update',
                'doneUrl' => '/admin/v2/setting/cloud',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/523/detail',
                'status' => false,
                'class' => CloudAppliedTask::class,
            ],
            'payment_applied_task' => [
                'name' => 'admin_v2.newcomer_task.payment_applied.title',
                'description' => 'admin_v2.newcomer_task.payment_applied.tip',
                'url' => '/admin/v2/setting/payment',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/525/detail',
                'status' => false,
                'class' => PaymentAppliedTask::class,
            ],
            'auth_setting_task' => [
                'name' => 'admin_v2.newcomer_task.auth_applied.title',
                'description' => 'admin_v2.newcomer_task.auth_applied.tip',
                'url' => '/admin/v2/setting/auth',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/526/detail',
                'status' => false,
                'class' => AuthSettingTask::class,
            ],
            'plugin_register_task' => [
                'name' => 'admin_v2.newcomer_task.plugin_applied.title',
                'description' => 'admin_v2.newcomer_task.plugin_applied.tip',
                'url' => '/admin/v2/app/center',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/524/detail',
                'status' => false,
                'class' => PluginRegisterTask::class,
            ],
            'course_created_task' => [
                'name' => 'admin_v2.newcomer_task.course_applied.title',
                'description' => 'admin_v2.newcomer_task.course_applied.tip',
                'url' => '/admin/v2/course_set/index',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/527/detail',
                'status' => false,
                'class' => CourseCreatedTask::class,
            ],
            'decoration_web_task' => [
                'name' => 'admin_v2.newcomer_task.decoration_applied.title',
                'description' => 'admin_v2.newcomer_task.decoration_applied.tip',
                'url' => '/admin/v2/setting/navigation?type=top',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/528/detail',
                'status' => false,
                'class' => DecorationWebTask::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $newcomerTasks = $this->getNewcomerTasks();
        foreach ($newcomerTasks as $taskName => $newcomerTask) {
            $container['newcomer.'.$taskName] = function ($biz) use ($newcomerTask) {
                return new $newcomerTask['class']($biz);
            };
        }
    }
}
