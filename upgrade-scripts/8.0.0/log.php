<?php

class log extends AbstractMigrate
{
    public function update($page)
    {
        $this->exec(
            "UPDATE `log` SET action = 'add_task' WHERE action = 'add_lesson' AND MODULE = 'course';"
        );
        $this->exec(
            "UPDATE `log` SET action = 'update_task' WHERE action = 'update_lesson' AND MODULE = 'course';"
        );
        $this->exec(
            "UPDATE `log` SET action = 'delete_task' WHERE action = 'delete_lesson' AND MODULE = 'course';"
        );
        $this->exec(
            "UPDATE `log` SET action = 'delete_taskLearn' WHERE action = 'delete_lessonLearn' AND MODULE = 'course';"
        );
    }
}
