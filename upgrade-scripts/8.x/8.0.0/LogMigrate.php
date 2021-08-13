<?php

class LogMigrate extends AbstractMigrate
{
    private function step1()
    {
        if (!$this->isIndexExist('log', 'actionAndModule')) {
            // $this->exec("alter table `log` add index actionAndModule (`action`, `MODULE`)");
        }
    }

    private function step2()
    {
        $this->exec(
            "UPDATE `log` SET action = 'add_task' WHERE action = 'add_lesson' AND MODULE = 'course';"
        );
    }

    private function step3()
    {
        $this->exec(
            "UPDATE `log` SET action = 'update_task' WHERE action = 'update_lesson' AND MODULE = 'course';"
        );
    }

    private function step4()
    {
        $this->exec(
            "UPDATE `log` SET action = 'delete_task' WHERE action = 'delete_lesson' AND MODULE = 'course';"
        );
    }

    private function step5()
    {
        $this->exec(
            "UPDATE `log` SET action = 'delete_taskLearn' WHERE action = 'delete_lessonLearn' AND MODULE = 'course';"
        );
    }

    public function update($page)
    {
        if ($page > 5) {
            return;
        }

        $method = "step{$page}";
        $this->$method();
        return $page+1;
    }
}
