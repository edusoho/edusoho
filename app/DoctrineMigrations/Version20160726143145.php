<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160726143145 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $sqls = array(
            "CREATE INDEX order_referer_uv_expiredTime_index ON order_referer (uv, expiredTime);",
            "CREATE INDEX open_course_member_ip_courseId_index ON open_course_member (ip, courseId);",
            "CREATE INDEX open_course_recommend_openCourseId_index ON open_course_recommend (openCourseId);",
            "CREATE INDEX block_code_orgId_index ON block (code, orgId);",
            "CREATE INDEX course_favorite_userId_courseId_type_index ON course_favorite (userId, courseId, type);"
        );
        foreach ($sqls as $sql){
            $this->addSql($sql);
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
