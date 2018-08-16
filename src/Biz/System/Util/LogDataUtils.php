<?php

namespace Biz\System\Util;

use Topxia\Service\Common\ServiceKernel;
use AppBundle\Common\ArrayToolkit;

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
        $changeFields = ArrayToolkit::changes($oldData, $newData);
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
        );

        $changeFields = self::serializeData($config, $changeFields);

        $changeFields['id'] = $oldData['id'];
        $changeFields['showTitle'] = self::getShowTitle($oldData);

        return $changeFields;
    }

    private function serializeUnsetChanges($newData)
    {
        $config = array(
            'createdTime',
            'updatedTime',
        );
        foreach ($config as $value) {
            if (isset($newData[$value])) {
                unset($newData[$value]);
            }
        }

        return $newData;
    }

    private function getShowTitle($oldData)
    {
        $showTitle = '';
        if (isset($oldData['title'])) {
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
}
