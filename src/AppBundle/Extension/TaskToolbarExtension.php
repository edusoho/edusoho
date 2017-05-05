<?php

namespace AppBundle\Extension;

class TaskToolbarExtension extends Extension
{
    public function getTaskToolbars()
    {
        return array(
            array(
                'code' => 'task-list',
                'name' => '目录',
                'icon' => 'es-icon-menu',
                'action' => 'course_task_show_plugin_task_list',
            ),
            array(
                'code' => 'note',
                'name' => '笔记',
                'icon' => 'es-icon-edit',
                'action' => 'course_task_plugin_note',
            ),
            array(
                'code' => 'question',
                'name' => '问答',
                'icon' => 'es-icon-help',
                'action' => 'course_task_plugin_threads',
            ),
        );
    }
}
