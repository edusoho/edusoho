<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140916150639 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE IF NOT EXISTS `user_sign` (
			`id` int(10) unsigned NOT NULL COMMENT 'Id',
			  `userId` int(10) unsigned NOT NULL COMMENT '用户Id',
			  `createdTime` int(10) unsigned NOT NULL COMMENT '签到时间'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

			--
			-- Indexes for dumped tables
			--

			--
			-- Indexes for table `user_sign`
			--
			ALTER TABLE `user_sign`
			 ADD PRIMARY KEY (`id`);

			--
			-- AUTO_INCREMENT for dumped tables
			--

			--
			-- AUTO_INCREMENT for table `user_sign`
			--
			ALTER TABLE `user_sign`
			MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id';");

        $this->addSql("CREATE TABLE IF NOT EXISTS `user_sign_related` (
			`id` int(10) unsigned NOT NULL,
			  `userId` int(10) unsigned NOT NULL,
			  `todayRank` int(11) NOT NULL COMMENT '今日签到排名',
			  `keepDays` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '连续签到天数'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

			--
			-- Indexes for dumped tables
			--

			--
			-- Indexes for table `user_sign_related`
			--
			ALTER TABLE `user_sign_related`
			 ADD PRIMARY KEY (`id`);

			--
			-- AUTO_INCREMENT for dumped tables
			--

			--
			-- AUTO_INCREMENT for table `user_sign_related`
			--
			ALTER TABLE `user_sign_related`
			MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
