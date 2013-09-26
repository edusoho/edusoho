<?php

namespace Topxia\Service\User\Tests\DataProvider;

use Topxia\Service\Common\TestDataProvider;

class UserDataProvider extends TestDataProvider {

    protected $rows = array(
    );

    protected $dropSql = 'DROP TABLE IF EXISTS category';

    protected $emptySql = 'TRUNCATE TABLE category';

    protected $createSql = "
            CREATE TABLE IF NOT EXISTS `category` (
          `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
          `code` varchar(64) NOT NULL,
          `name` varchar(255) NOT NULL,
          `path` varchar(255) NOT NULL,
          `weight` int(11) NOT NULL DEFAULT '0',
          `groupId` int(10) unsigned NOT NULL,
          `parentId` int(10) unsigned NOT NULL DEFAULT '0',
          PRIMARY KEY (`id`),
          UNIQUE KEY `uri` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
    ";
}