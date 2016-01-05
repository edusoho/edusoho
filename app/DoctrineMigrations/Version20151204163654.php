<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151204163654 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM `theme_config` where `name`='简墨';");
        $this->addSql("INSERT INTO `theme_config`  (`id`, `name`, `config`, `confirmConfig`, `allConfig`, `updatedTime`, `createdTime`, `updatedUserId`)
            VALUES (
                NULL,
                '简墨',
                '{\"maincolor\":\"default\",\"navigationcolor\":\"default\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"12\",\"orderBy\":\"latest\",\"categoryId\":0,\"code\":\"course-grid-with-condition-index\",\"categoryCount\":\"4\",\"defaultTitle\":\"网校课程\",\"subTitle\":\"\",\"defaultSubTitle\":\"精选网校课程，满足你的学习兴趣。\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"近期直播\",\"subTitle\":\"\",\"defaultSubTitle\":\"实时跟踪直播课程，避免课程遗漏。\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"中部banner\",\"id\":\"middle-banner\"},{\"title\":\"\",\"count\":\"4\",\"code\":\"recommend-classroom\",\"defaultTitle\":\"推荐班级\",\"subTitle\":\"\",\"defaultSubTitle\":\"班级化学习体系，给你更多的课程相关服务。\",\"id\":\"RecommendClassrooms\"},{\"title\":\"\",\"count\":\"6\",\"code\":\"groups\",\"defaultTitle\":\"动态\",\"subTitle\":\"\",\"defaultSubTitle\":\"参与小组，结交更多同学，关注课程动态。\",\"select1\":\"checked\",\"select2\":\"checked\",\"select3\":\"\",\"select4\":\"\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"推荐教师\",\"subTitle\":\"\",\"defaultSubTitle\":\"名师汇集，保证教学质量与学习效果。\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"simple\"}',
                '{\"maincolor\":\"default\",\"navigationcolor\":\"default\",\"blocks\":{\"left\":[{\"title\":\"\",\"count\":\"12\",\"orderBy\":\"latest\",\"categoryId\":0,\"code\":\"course-grid-with-condition-index\",\"categoryCount\":\"4\",\"defaultTitle\":\"网校课程\",\"subTitle\":\"\",\"defaultSubTitle\":\"精选网校课程，满足你的学习兴趣。\",\"id\":\"latestCourse\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"live-course\",\"defaultTitle\":\"近期直播\",\"subTitle\":\"\",\"defaultSubTitle\":\"实时跟踪直播课程，避免课程遗漏。\",\"id\":\"RecentLiveCourses\"},{\"title\":\"\",\"count\":\"\",\"code\":\"middle-banner\",\"defaultTitle\":\"中部banner\",\"id\":\"middle-banner\"},{\"title\":\"\",\"count\":\"4\",\"code\":\"recommend-classroom\",\"defaultTitle\":\"推荐班级\",\"subTitle\":\"\",\"defaultSubTitle\":\"班级化学习体系，给你更多的课程相关服务。\",\"id\":\"RecommendClassrooms\"},{\"title\":\"\",\"count\":\"6\",\"code\":\"groups\",\"defaultTitle\":\"动态\",\"subTitle\":\"\",\"defaultSubTitle\":\"参与小组，结交更多同学，关注课程动态。\",\"select1\":\"checked\",\"select2\":\"checked\",\"select3\":\"\",\"select4\":\"\",\"id\":\"hotGroups\"},{\"title\":\"\",\"count\":\"4\",\"categoryId\":\"\",\"code\":\"recommend-teacher\",\"defaultTitle\":\"推荐教师\",\"subTitle\":\"\",\"defaultSubTitle\":\"名师汇集，保证教学质量与学习效果。\",\"id\":\"RecommendTeachers\"}]},\"bottom\":\"simple\"}',
                NULL,
                '1449218369',
                '1449218369',
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
