<?php 

class DeletePlugins extends AbstractMigrate
{
    public function update($page)
    {
        $apps = $this->getConnection()->fetchAll("select * from cloud_app where code <> 'MAIN';");
        foreach ($apps as $key => $app) {
            $this->getConnection()->exec("delete from cloud_app where id = {$app['id']};");
        }
    }
}
