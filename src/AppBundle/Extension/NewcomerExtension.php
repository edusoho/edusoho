<?php

namespace AppBundle\Extension;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class NewcomerExtension extends Extension implements ServiceProviderInterface
{
    public function getNewcomerTasks()
    {
        return array(
            'cloud_applied_task' => array(
                'name' => 'admin_v2.newcomer_task.cloud_applied.title',
                'description' => 'admin_v2.newcomer_task.cloud_applied.tip',
                'url' => '/admin/v2/setting/cloud/key/update',
                'doneUrl' => '/admin/v2/setting/cloud',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/523/detail',
                'status' => false,
                'class' => 'Biz\NewComer\CloudAppliedTask',
            ),
            'payment_applied_task' => array(
                'name' => 'admin_v2.newcomer_task.payment_applied.title',
                'description' => 'admin_v2.newcomer_task.payment_applied.tip',
                'url' => '/admin/v2/setting/payment',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/525/detail',
                'status' => false,
                'class' => 'Biz\NewComer\PaymentAppliedTask',
            ),
            'auth_setting_task' => array(
                'name' => 'admin_v2.newcomer_task.auth_applied.title',
                'description' => 'admin_v2.newcomer_task.auth_applied.tip',
                'url' => '/admin/v2/setting/auth',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/526/detail',
                'status' => false,
                'class' => 'Biz\NewComer\AuthSettingTask',
            ),
            'plugin_register_task' => array(
                'name' => 'admin_v2.newcomer_task.plugin_applied.title',
                'description' => 'admin_v2.newcomer_task.plugin_applied.tip',
                'url' => '/admin/v2/app/center',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/524/detail',
                'status' => false,
                'class' => 'Biz\NewComer\PluginRegisterTask',
            ),
            'course_created_task' => array(
                'name' => 'admin_v2.newcomer_task.course_applied.title',
                'description' => 'admin_v2.newcomer_task.course_applied.tip',
                'url' => '/admin/v2/course_set/index',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/527/detail',
                'status' => false,
                'class' => 'Biz\NewComer\CourseCreatedTask',
            ),
            'decoration_web_task' => array(
                'name' => 'admin_v2.newcomer_task.decoration_applied.title',
                'description' => 'admin_v2.newcomer_task.decoration_applied.tip',
                'url' => '/admin/v2/setting/navigation?type=top',
                'guideUrl' => 'http://www.qiqiuyu.com/faq/528/detail',
                'status' => false,
                'class' => 'Biz\NewComer\DecorationWebTask',
            ),
        );
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
