<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Topxia\Service\Common\ServiceKernel;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150515152421 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("select 1 from dual");
        $this->getSettingService()->set("crontab_next_executed_time", time());
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    private function getSettingService()
    {
        return $this->initServiceKernel()->createService('System.SettingService');
    }

    private function initServiceKernel()
    {
        global $kernel;

        $serviceKernel = ServiceKernel::create('dev', false);
        $serviceKernel->setParameterBag($kernel->getContainer()->getParameterBag());
        $serviceKernel->setConnection($kernel->getContainer()->get('database_connection'));
        return $serviceKernel;
    }

}
