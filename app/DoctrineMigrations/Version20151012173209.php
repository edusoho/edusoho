<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151012173209 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
         $this->addSql("update upload_files set usedCount=0");
         $this->addSql("update upload_files as file, 
                        (SELECT mediaId,COUNT(id) coun from course_lesson where type<>'live' and mediaSource='self' and mediaId is not null group by mediaId) as t1
                        set file.usedCount = t1.coun+file.usedCount where t1.mediaId = file.id");
         $this->addSql("update upload_files as file, 
                        (SELECT fileId,COUNT(id) coun from course_material group by fileId) as t2 
                        set file.usedCount = t2.coun+file.usedCount where t2.fileId = file.id");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
