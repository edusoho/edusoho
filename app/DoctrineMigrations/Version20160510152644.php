<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160510152644 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            "ALTER TABLE `announcement` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `article` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `article_category` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `article_like` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `batch_notification` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `blacklist` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `block` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `block_history` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cache` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `card` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cash_account` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cash_change` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cash_flow` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cash_orders` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cash_orders_log` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `category` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `category_group` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `classroom` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `classroom_courses` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `classroom_member` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `classroom_review` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cloud_app` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cloud_app_logs` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `cloud_data` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `comment` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `content` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `coupon` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `coupon_batch` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_chapter` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_draft` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_favorite` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_lesson` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_lesson_learn` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_lesson_replay` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_lesson_view` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_material` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_member` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_note` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_note_like` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_review` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_thread` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `course_thread_post` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `crontab_job` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `dictionary` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `dictionary_item` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `discount` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `discount_item` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `discovery_column` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `file` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `file_group` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `friend` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `groups` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `groups_member` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `groups_thread` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `groups_thread_collect` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `groups_thread_goods` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `groups_thread_post` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `groups_thread_trade` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `installed_packages` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `invite_record` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `ip_blacklist` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `keyword` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `keyword_banlog` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `location` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `log` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `marker` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `message` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `message_conversation` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `message_relation` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `migration_versions` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `mobile_device` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `money_card` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `money_card_batch` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `money_record` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `navigation` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `notification` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `order_log` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `order_refund` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `orders` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `org` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `question` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `question_category` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `question_favorite` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `question_marker` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `question_marker_result` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `recent_post_num` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `sessions` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `setting` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `shortcut` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `sign_card` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `sign_target_statistics` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `sign_user_log` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `sign_user_statistics` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `status` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `tag` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `task` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `testpaper` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `testpaper_item` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `testpaper_item_result` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `testpaper_result` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `theme_config` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `thread` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `thread_member` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `thread_post` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `thread_vote` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `upgrade_logs` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `upload_file_inits` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `upload_files` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `upload_files_collection` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `upload_files_share` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `upload_files_share_history` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `upload_files_tag` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user_approval` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user_bind` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user_field` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user_fortune_log` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user_pay_agreement` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user_profile` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user_secure_question` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `user_token` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `vip` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `vip_history` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';
            ALTER TABLE `vip_level` ADD `orgCode` varchar(255) NOT NULL DEFAULT '1.' COMMENT '组织机构编码';"
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
