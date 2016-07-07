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
        if ($schema->hasTable('upload_file_inits')) {
            $sql    = "select max(id) as maxId from upload_files;";
            $uploadFileMaxId = $this->connection->fetchAssoc($sql);

            $sql    = "SELECT auto_increment FROM information_schema.`TABLES` WHERE table_schema='".$schema->getName()."' AND table_name = 'upload_file_inits';";
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

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
