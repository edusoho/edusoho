<?php

use Phpmig\Migration\Migration;

class Oauth2 extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            CREATE TABLE IF NOT EXISTS `oauth_access_token` (
            `token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
            `client_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
            `user_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
            `expires` datetime NOT NULL,
            `scope` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`token`),
            KEY `IDX_F7FA86A419EB6921` (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

            CREATE TABLE IF NOT EXISTS `oauth_authorization_code` (
              `code` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
              `client_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
              `expires` datetime NOT NULL,
              `user_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
              `redirect_uri` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
              `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              `id_token` varchar(2000) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`code`),
              KEY `IDX_793B081719EB6921` (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

            CREATE TABLE IF NOT EXISTS `oauth_client` (
              `client_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
              `client_secret` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
              `redirect_uri` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:simple_array)',
              `grant_types` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
              `scopes` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
              PRIMARY KEY (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

            CREATE TABLE IF NOT EXISTS `oauth_client_public_key` (
              `client_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
              `public_key` longtext COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`client_id`),
              UNIQUE KEY `UNIQ_4D89651719EB6921` (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

            CREATE TABLE IF NOT EXISTS `oauth_refresh_token` (
              `token` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
              `client_id` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
              `user_id` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
              `expires` datetime NOT NULL,
              `scope` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
              PRIMARY KEY (`token`),
              KEY `IDX_55DCF75519EB6921` (`client_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            
            CREATE TABLE IF NOT EXISTS `oauth_scope` (
              `scope` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
              PRIMARY KEY (`scope`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            
            
            ALTER TABLE `oauth_access_token`
              ADD CONSTRAINT `FK_F7FA86A419EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`client_id`) ON DELETE CASCADE;
            
            ALTER TABLE `oauth_authorization_code`
              ADD CONSTRAINT `FK_793B081719EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`client_id`) ON DELETE CASCADE;
            
            ALTER TABLE `oauth_client_public_key`
              ADD CONSTRAINT `FK_4D89651719EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`client_id`) ON DELETE CASCADE;
            
            ALTER TABLE `oauth_refresh_token`
              ADD CONSTRAINT `FK_55DCF75519EB6921` FOREIGN KEY (`client_id`) REFERENCES `oauth_client` (`client_id`) ON DELETE CASCADE;
        ");
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $biz = $this->getContainer();
        $biz['db']->exec("
            DROP TABLE IF EXISTS `oauth_access_token`;
            DROP TABLE IF EXISTS `oauth_authorization_code`;
            DROP TABLE IF EXISTS `oauth_client_public_key`;
            DROP TABLE IF EXISTS `oauth_refresh_token`;
            DROP TABLE IF EXISTS `oauth_scope`;
        ");
    }
}
