<?php

namespace Biz\System\Util;

use Topxia\Service\Common\ServiceKernel;
use Biz\Course\Util\CourseTitleUtils;
use AppBundle\Common\PluginVersionToolkit;
use Symfony\Component\Yaml\Yaml;

class LogDataUtils
{
    public static function getYmlConfig()
    {
        $config = array();

        $paths = self::getPermissionConfig();

        $permissions = array();
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            $menus = Yaml::parse(file_get_contents($path));
            if (empty($menus)) {
                continue;
            }

            $menus = self::loadPermissionsFromConfig($menus);
            $permissions = array_merge($permissions, $menus);
        }

        if (array_key_exists('config', $permissions)) {
            $config = $permissions['config'];
        }

        return $config;
    }

    protected function loadPermissionsFromConfig($parents)
    {
        $menus = array();

        foreach ($parents as $key => $value) {
            $value['code'] = $key;
            $menus[$key] = $value;

            if (isset($value['children'])) {
                $childrenMenu = $value['children'];

                unset($value['children']);

                foreach ($childrenMenu as $childKey => $childValue) {
                    $childValue['parent'] = $key;
                    $menus = array_merge($menus, $this->loadPermissionsFromConfig(array($childKey => $childValue)));
                }
            }
        }

        return $menus;
    }

    private function getPermissionConfig()
    {
        $configPaths = array();

        $rootDir = ServiceKernel::instance()->getParameter('kernel.root_dir');
        $files = array(
            $rootDir.'/../src/AppBundle/Resources/config/log_modules.yml',
            $rootDir.'/../src/CustomBundle/Resources/config/log_modules.yml',
        );

        foreach ($files as $filepath) {
            if (is_file($filepath)) {
                $configPaths[] = $filepath;
            }
        }

        $count = self::getAppService()->findAppCount();
        $apps = self::getAppService()->findApps(0, $count);

        foreach ($apps as $app) {
            if ('plugin' != $app['type']) {
                continue;
            }

            if ('MAIN' !== $app['code'] && $app['protocol'] < 3) {
                continue;
            }

            if (!PluginVersionToolkit::dependencyVersion($app['code'], $app['version'])) {
                continue;
            }

            $code = ucfirst($app['code']);
            $configPaths[] = "{$rootDir}/../plugins/{$code}Plugin/Resources/config/log_modules.yml";
        }

        return $configPaths;
    }

    public static function getLogConfig()
    {
        $config = array(
            'course' => array(
                'create' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'modalField' => 'all',
                ),
                'create_course' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update_course' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'modalField' => 'all',
                ),
                'update_thread' => array(
                    'modalField' => 'all',
                ),
                'add_student' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                        '%title%' => 'title',
                        '%nickname%' => 'nickname',
                        '%remark%' => 'remark',
                    ),
                ),
                'remove_student' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                        '%title%' => 'title',
                        '%nickname%' => 'nickname',
                    ),
                ),
                'publish' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'close' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'create_lesson' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_show',
                            'param' => array(
                                'id' => '0.courseId',
                            ),
                        ),
                        '%title%' => 'title',
                    ),
                ),
                'add_task' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                    ),
                ),
                'update_teacher' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
            ),
            'classroom' => array(
                'create' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                    'modalField' => 'all',
                ),
                'add_student' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'classroomId',
                            ),
                        ),
                        '%title%' => 'title',
                        '%nickname%' => 'nickname',
                        '%remark%' => 'remark',
                    ),
                ),
                'remove_student' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'classroomId',
                            ),
                        ),
                        '%title%' => 'title',
                        '%nickname%' => 'nickname',
                    ),
                ),
                'add_course' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'classroomId',
                            ),
                        ),
                        '%title%' => 'title',
                        '%courseSetTitle%' => 'courseSetTitle',
                    ),
                ),
                'delete_course' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'classroomId',
                            ),
                        ),
                        '%title%' => 'title',
                        '%courseSetTitle%' => 'courseSetTitle',
                    ),
                ),
            ),
            'article' => array(
                'create' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'article_detail',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'article_detail',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update_property' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'article_detail',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'cancel_property' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'article_detail',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
            ),
            'open_course' => array(
                'create_course' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'open_course_show',
                            'param' => array(
                                'courseId' => 'id',
                            ),
                        ),
                    ),
                ),
                'update_course' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'open_course_show',
                            'param' => array(
                                'courseId' => 'id',
                            ),
                        ),
                    ),
                    'modalField' => 'all',
                ),
                'add_lesson' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'open_course_show',
                            'param' => array(
                                'courseId' => 'courseId',
                            ),
                        ),
                    ),
                ),
            ),
            'group' => array(
                'create_thread' => array(
                    'templateParam' => array(
                        '%url%' => array(
                            'type' => 'url',
                            'path' => 'group_thread_show',
                            'param' => array(
                                'id' => 'groupId',
                                'threadId' => 'id',
                            ),
                        ),
                    ),
                ),
            ),
            'thread' => array(
                'update' => array(
                    'modalField' => 'all',
                ),
            ),
            'user' => array(
                'password-changed' => array(
                    'templateParam' => array(
                        '%title%' => 'title',
                    ),
                ),
                'pay-password-changed' => array(
                    'templateParam' => array(
                        '%title%' => 'title',
                    ),
                ),
                'password-security-answers' => array(
                    'templateParam' => array(
                        '%title%' => 'title',
                    ),
                ),
                'verifiedMobile-changed' => array(
                    'modalField' => 'all',
                ),
                'email-changed' => array(
                    'modalField' => 'all',
                ),
                'update' => array(
                    'modalField' => 'all',
                ),
                'nickname_change' => array(
                    'modalField' => 'all',
                ),
                'change_role' => array(
                    'modalField' => 'all',
                ),
            ),
            'system' => array(
                'update_settings.site' => array(
                    'modalField' => 'all',
                ),
                'update_settings.theme' => array(
                    'modalField' => 'all',
                ),
                'update_settings.cloud_email_crm' => array(
                    'modalField' => 'all',
                ),
                'update_settings.mailer' => array(
                    'modalField' => 'all',
                ),
                'update_settings.esBar' => array(
                    'modalField' => 'all',
                ),
                'update_settings.default' => array(
                    'modalField' => 'all',
                ),
                'update_settings.security' => array(
                    'modalField' => 'all',
                ),
                'update_settings.login_bind' => array(
                    'modalField' => 'all',
                ),
                'update_settings.user_partner' => array(
                    'modalField' => 'all',
                ),
                'update_settings.auth' => array(
                    'modalField' => 'all',
                ),
                'update_settings.course' => array(
                    'modalField' => 'all',
                ),
                'update_settings.message' => array(
                    'modalField' => 'all',
                ),
                'update_settings.course_default' => array(
                    'modalField' => 'all',
                ),
                'update_settings.questions' => array(
                    'modalField' => 'all',
                ),
                'update_settings.classroom' => array(
                    'modalField' => 'all',
                ),
                'update_settings.article' => array(
                    'modalField' => 'all',
                ),
                'update_settings.group' => array(
                    'modalField' => 'all',
                ),
                'update_settings.invite' => array(
                    'modalField' => 'all',
                ),
                'update_settings.payment' => array(
                    'modalField' => 'all',
                ),
                'update_settings.coin' => array(
                    'modalField' => 'all',
                ),
                'update_settings.refund' => array(
                    'modalField' => 'all',
                ),
                'update_settings.blacklist_ip' => array(
                    'modalField' => 'all',
                ),
                'update_settings.post_num_rules' => array(
                    'modalField' => 'all',
                ),
            ),
        );

        return $config;
    }

    public static function getLogDefaultConfig()
    {
        $config = array(
            '%title%' => array(
                'showTitle',
                'title',
                'courseSetTitle',
                'name',
                'content',
                'filename',
                'stem',
            ),
        );

        return $config;
    }

    public static function trans($message, $module, $action)
    {
        $prefixs = self::getTransPrefix($module, $action);
        foreach ($prefixs as $prefix) {
            $transMessage = $prefix.'.'.$message;
            $trans = ServiceKernel::instance()->trans($transMessage, array(), null, null);
            if ($trans != $transMessage) {
                return $trans;
            }
        }

        return $message;
    }

    public static function serializeChanges($oldData, $newData)
    {
        $newData = self::serializeUnsetChanges($newData);
        $oldData = self::initArray($oldData);
        $newData = self::initArray($newData);
        $changeFields = self::arrayChanges($oldData, $newData);
        if (empty($changeFields['before']) && empty($changeFields['after'])) {
            return array();
        }
        $config = array(
            'buyExpiryTime' => array(
                'timeConvent',
            ),
            'expiryStartDate' => array(
                'timeConvent',
            ),
            'expiryEndDate' => array(
                'timeConvent',
            ),
            'password' => array(
                'passwordSetBlank',
            ),
        );

        $changeFields = self::serializeData($config, $changeFields);

        $changeFields = self::getShowField($changeFields, $oldData);

        return $changeFields;
    }

    private function initArray($array)
    {
        if (!is_array($array)) {
            $array = array($array);
        }

        return $array;
    }

    private function arrayChanges(array $before = array(), array $after)
    {
        $changes = array('before' => array(), 'after' => array());

        foreach ($after as $key => $value) {
            if (!isset($before[$key])) {
                $before[$key] = '';
            }

            if ($value != $before[$key]) {
                $changes['before'][$key] = $before[$key];
                $changes['after'][$key] = $value;
            }
        }

        return $changes;
    }

    private function serializeUnsetChanges($newData)
    {
        $config = array(
            'createdTime',
            'updatedTime',
            'updateTime',
            'coin_picture_50_50',
            'coin_picture_30_30',
            'coin_picture_20_20',
            'coin_picture_10_10',
        );
        foreach ($config as $value) {
            if (isset($newData[$value])) {
                unset($newData[$value]);
            }
        }

        return $newData;
    }

    private function getShowField($changeFields, $oldData)
    {
        $changeFields['id'] = self::getShowId($oldData);
        $changeFields['showTitle'] = self::getShowTitle($oldData);

        return $changeFields;
    }

    private function getShowId($oldData)
    {
        $showId = '';
        if (isset($oldData['id'])) {
            $showId = $oldData['id'];
        }

        return $showId;
    }

    private function getShowTitle($oldData)
    {
        $showTitle = '';
        if (isset($oldData['email'])) {
            $showTitle = $oldData['email'];
        } elseif (isset($oldData['title'])) {
            $showTitle = $oldData['title'];
            if (isset($oldData['courseSetTitle'])) {
                $showTitle = CourseTitleUtils::getDisplayedTitle($oldData);
            }
        } elseif (isset($oldData['name'])) {
            $showTitle = $oldData['name'];
        }

        return $showTitle;
    }

    private function serializeData($config, $changeFields)
    {
        $data = array();
        $oldData = $changeFields['before'];
        $newData = $changeFields['after'];

        foreach ($oldData as $key => $oldValue) {
            $newValue = $newData[$key];
            $old = $oldValue;
            $new = $newValue;

            if (isset($config[$key])) {
                foreach ($config[$key] as $function) {
                    $old = self::$function($oldValue);
                    $new = self::$function($newValue);
                }
            }

            $data[$key] = array(
                'old' => $old,
                'new' => $new,
            );
        }

        return $data;
    }

    public static function getTransPrefix($module, $action)
    {
        return array(
            'log.attr.'.$module.'.'.$action,
            'log.attr.'.$module,
            'log.attr',
        );
    }

    private function timeConvent($time)
    {
        if ($time > 10000) {
            $time = date('Y-m-d H:i:s', $time);
        }

        return $time;
    }

    private function passwordSetBlank($password)
    {
        if (!empty($password)) {
            $password = '******';
        }

        return $password;
    }

    protected function getAppService()
    {
        return self::getServiceKernel()->createService('CloudPlatform:AppService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
