<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151126173858 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("INSERT INTO `theme_config`  (`id`, `name`, `config`, `confirmConfig`, `allConfig`, `updatedTime`, `createdTime`, `updatedUserId`)
            VALUES (
                NULL,
                '简墨',
                '{\"maincolor\":\"default\",\"navigationcolor\":\"default\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"orderBy\":\"latest\",\"code\":\"course-grid-with-condition-index\",\"defaultTitle\":\"\u8bfe\u7a0b\u7ec4\u4ef6\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"\u76f4\u64ad\u7ec4\u4ef6\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"\u4e2d\u90e8banner\",\"id\":\"middle-banner
                \"},{\"title\":\"\",\"count\":\"6\",\"code\":\"groups\",\"defaultTitle\":\"\u52a8\u6001\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"\u63a8\u8350\u6559\u5e08\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"\"}',
                '{\"maincolor\":\"default\"}',
                NULL,
                '14444444',
                '14444444',
                '1'
            );"
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
