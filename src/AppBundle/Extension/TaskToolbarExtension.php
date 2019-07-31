<?php

namespace AppBundle\Extension;

use Biz\System\Service\SettingService;

class TaskToolbarExtension extends Extension
{
    public function getTaskToolbars()
    {
        $taskToolBars = array();
        $taskToolBars[] = array(
            'code' => 'task-list',
            'name' => 'course.task_toolbar.tasks',
            'icon' => 'es-icon-menu',
            'action' => 'course_task_show_plugin_task_list',
        );
        $course = $this->getSettingService()->get('course', array());

        if (!empty($course['show_note']) || !isset($course['show_note'])) {
            $taskToolBars[] = array(
                'code' => 'note',
                'name' => 'course.task_toolbar.notes',
                'icon' => 'es-icon-edit',
                'action' => 'course_task_plugin_note',
            );
        }

        if (!empty($course['show_question']) || !isset($course['show_question'])) {
            $taskToolBars[] = array(
                'code' => 'question',
                'name' => 'course.task_toolbar.questions',
                'icon' => 'es-icon-help',
                'action' => 'course_task_plugin_threads',
            );
        }

        return $taskToolBars;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
