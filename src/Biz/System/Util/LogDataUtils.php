<?php

namespace Biz\System\Util;

use Topxia\Service\Common\ServiceKernel;

class LogDataUtils
{
    public static function getTransConfig()
    {
        $config = array(
            'course' => array(
                'create' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'create_course' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update_course' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'delete_course' => array(
                ),
                'add_student' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                        '%nickname%' => 'nickname',
                        '%remark%' => 'remark',
                    ),
                ),
                'remove_student' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => 'title',
                        '%nickname%' => 'nickname',
                    ),
                ),
                'create_thread' => array(
                ),
                'delete_thread' => array(
                ),
                'publish' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'close' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_set_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'create_lesson' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => '0.courseId',
                            ),
                        ),
                    ),
                    'getValue' => array(
                        '%title%' => '0.title',
                    ),
                ),
                'add_task' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'courseId',
                            ),
                        ),
                    ),
                ),
                'update_teacher' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'course_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'add_question' => array(
                ),
                'delete_question' => array(
                ),
                'add_testpaper' => array(
                ),
                'delete_testpaper' => array(
                ),
                'delete_lesson' => array(
                ),
                'delete' => array(
                ),
                'create_chapter' => array(
                ),
                'delete_chapter' => array(
                ),
                'delete_review' => array(
                ),
                'delete_task' => array(
                ),
                'update_task' => array(
                ),
                'recommend' => array(
                ),
                'cancel_recommend' => array(
                ),
                'update_draft' => array(
                ),
            ),
            'classroom' => array(
                'create' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'classroom_show',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'delete' => array(
                ),
            ),
            'category' => array(
                'create' => array(
                ),
                'delete' => array(
                ),
                'update' => array(
                ),
            ),
            'content' => array(
                'create' => array(
                ),
                'delete' => array(
                ),
                'update' => array(
                ),
                'trash' => array(
                ),
                'publish' => array(
                ),
            ),
            'upload_file' => array(
                'create' => array(
                ),
                'delete' => array(
                ),
            ),
            'article' => array(
                'create' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'article_detail',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'article_detail',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'update_property' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'article_detail',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'cancel_property' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'article_detail',
                            'param' => array(
                                'id' => 'id',
                            ),
                        ),
                    ),
                ),
                'trash' => array(
                ),
                'removeThumb' => array(
                ),
                'publish' => array(
                ),
                'unpublish' => array(
                ),
            ),
            'tag' => array(
                'create' => array(
                ),
                'delete' => array(
                ),
                'update' => array(
                ),
            ),
            'tagGroup' => array(
                'create' => array(
                ),
                'delete' => array(
                ),
                'update' => array(
                ),
            ),
            'announcement' => array(
                'create' => array(
                ),
                'delete' => array(
                ),
            ),
            'open_course' => array(
                'create_course' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'open_course_show',
                            'param' => array(
                                'courseId' => 'id',
                            ),
                        ),
                    ),
                ),
                'update_course' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'open_course_show',
                            'param' => array(
                                'courseId' => 'id',
                            ),
                        ),
                    ),
                ),
                'pulish_course' => array(
                ),
                'close_course' => array(
                ),
                'add_lesson' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'open_course_show',
                            'param' => array(
                                'courseId' => 'courseId',
                            ),
                        ),
                    ),
                ),
                'update_lesson' => array(
                ),
                'update_picture' => array(
                ),
                'delete_lesson' => array(
                ),
                'delete_course' => array(
                ),
                'update_teacher' => array(
                ),
            ),
            'group' => array(
                'create_thread' => array(
                    'generateUrl' => array(
                        '%url%' => array(
                            'path' => 'group_thread_show',
                            'param' => array(
                                'id' => 'groupId',
                                'threadId' => 'id',
                            ),
                        ),
                    ),
                ),
                'update_thread' => array(
                ),
                'delete_thread' => array(
                ),
                'open_thread' => array(
                ),
                'close_thread' => array(
                ),
            ),
            'marker' => array(
                'create' => array(
                ),
                'delete' => array(
                ),
                'delete_question' => array(
                ),
            ),
            'role' => array(
                'create' => array(
                ),
                'delete' => array(
                ),
                'update' => array(
                ),
            ),
            'user' => array(
                'password-changed' => array(
                    'getValue' => array(
                        '%title%' => 'email',
                    ),
                ),
                'pay-password-changed' => array(
                    'getValue' => array(
                        '%title%' => 'email',
                    ),
                ),
                'password-security-answers' => array(
                    'getValue' => array(
                        '%title%' => 'email',
                    ),
                ),
                'verifiedMobile-changed' => array(
                ),
                'email-changed' => array(
                ),
                'update' => array(
                ),
                'nickname_change' => array(
                ),
                'avatar-changed' => array(
                ),
                'change_role' => array(
                ),
                'lock' => array(
                ),
                'unlock' => array(
                ),
            ),
            'system' => array(
                'update_settings.site' => array(
                ),
                'update_settings.theme' => array(
                ),
                'update_settings.cloud_email_crm' => array(
                ),
                'update_settings.mailer' => array(
                ),
                'update_settings.consult' => array(
                ),
                'update_settings.esBar' => array(
                ),
                'update_settings.default' => array(
                ),
                'update_settings.security' => array(
                ),
                'update_settings.login_bind' => array(
                ),
                'update_settings.user_partner' => array(
                ),
                'update_settings.auth' => array(
                ),
                'update_settings.course' => array(
                ),
                'update_settings.message' => array(
                ),
                'update_settings.course_default' => array(
                ),
                'update_settings.questions' => array(
                ),
                'update_settings.classroom' => array(
                ),
                'update_settings.article' => array(
                ),
                'update_settings.group' => array(
                ),
                'update_settings.invite' => array(
                ),
                'update_settings.payment' => array(
                ),
                'update_settings.coin' => array(
                ),
                'update_settings.refund' => array(
                ),
                'update_settings.blacklist_ip' => array(
                ),
                'update_settings.post_num_rules' => array(
                ),
            ),
        );

        return $config;
    }

    public static function getValueConfig()
    {
        $config = array(
            '%title%' => array(
                'showTitle',
                'title',
                'name',
                'content',
                'filename',
                'stem',
            ),
        );

        return $config;
    }

    public static function shouldShowModal($module, $action)
    {
        $showModals = array(
            'course' => array(
                'update',
                'update_course',
            ),
            'classroom' => array(
                'update',
            ),
            'open_course' => array(
                'update_course',
            ),
            'user' => array(
                'verifiedMobile-changed',
                'email-changed',
                'update',
                'nickname_change',
                'change_role',
            ),
            'system' => array(
                'update_settings.site',
                'update_settings.theme',
                'update_settings.cloud_email_crm',
                'update_settings.mailer',
                'update_settings.esBar',
                'update_settings.default',
                'update_settings.security',
                'update_settings.login_bind',
                'update_settings.user_partner',
                'update_settings.auth',
                'update_settings.course',
                'update_settings.message',
                'update_settings.course_default',
                'update_settings.questions',
                'update_settings.classroom',
                'update_settings.article',
                'update_settings.group',
                'update_settings.invite',
                'update_settings.payment',
                'update_settings.coin',
                'update_settings.refund',
                'update_settings.blacklist_ip',
                'update_settings.post_num_rules',
            ),
        );
        if (array_key_exists($module, $showModals)) {
            if (in_array($action, $showModals[$module])) {
                return true;
            }
        }

        return false;
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
}
