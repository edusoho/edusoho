<?php 

class DeletePlugins extends AbstractMigrate
{
    public function update($page)
    {
        $this->exec("delete from cloud_app where code <> 'MAIN';");
    }
}
