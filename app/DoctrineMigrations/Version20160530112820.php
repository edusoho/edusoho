<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Topxia\Service\Common\ServiceKernel;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160530112820 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        if ($this->isTableExist('upload_file_inits')) {
            $sql    = "select max(id) as maxId from upload_files;";
            $uploadFileMaxId = $this->connection->fetchAssoc($sql);

            $sql    = "SELECT auto_increment FROM information_schema.`TABLES` WHERE table_schema='".$this->getSchema()."' AND table_name = 'upload_file_inits';";
            $uploadFileInitMaxId = $this->connection->fetchAssoc($sql);

            if(empty($uploadFileMaxId['maxId'])) {
                return;
            }

            if($uploadFileMaxId['maxId']<$uploadFileInitMaxId['auto_increment']){
                return;
            }

            $start = $uploadFileMaxId['maxId'] + 10;
            $this->addSql("alter table upload_file_inits AUTO_INCREMENT = {$start};");
        }


    }


    protected function isTableExist($table)
    {
        $sql    = "SHOW TABLES LIKE '{$table}'";
        $result = $this->connection->fetchAssoc($sql);
        return empty($result) ? false : true;
    }

    private function getSchema()
    {
        global $kernal;

        $database = $kernal->getContainer()->getParameter('database_name');
        return $database;
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
