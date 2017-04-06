<?php

class PluginMigrate extends AbstractMigrate
{
    public function update($page)
    {
        $this->exec(
            "
            delete from cloud_app where code = 'Homework';
            "
        );
    }
}
