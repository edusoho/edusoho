<?php

namespace AppBundle\Extension;

class TaskToolbarExtension extends Extension
{
    public function getTaskToolbars()
    {
        return array(
            array(
                'code' => 'task-list',
                'name' => 'course.task_toolbar.tasks',
                'icon' => 'es-icon-menu',
                'action' => 'course_task_show_plugin_task_list',
            ),
            array(
                'code' => 'note',
                'name' => 'course.task_toolbar.notes',
                'icon' => 'es-icon-edit',
                'action' => 'course_task_plugin_note',
            ),
            array(
                'code' => 'question',
                'name' => 'course.task_toolbar.questions',
                'icon' => 'es-icon-help',
                'action' => 'course_task_plugin_threads',
            ),
        );
    }
}
