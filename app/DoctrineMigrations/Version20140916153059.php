<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140916153059 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("CREATE TABLE IF NOT EXISTS `class_sign_related` (
			`id` int(10) unsigned NOT NULL,
			  `classId` int(10) unsigned NOT NULL COMMENT '班级Id',
			  `date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '班级签到时间',
			  `signedNum` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '今日签到人数'
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

			--
			-- Indexes for dumped tables
			--

			--
			-- Indexes for table `class_sign_related`
			--
			ALTER TABLE `class_sign_related`
			 ADD PRIMARY KEY (`id`);

			--
			-- AUTO_INCREMENT for dumped tables
			--

			--
			-- AUTO_INCREMENT for table `class_sign_related`
			--
			ALTER TABLE `class_sign_related`
			MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
