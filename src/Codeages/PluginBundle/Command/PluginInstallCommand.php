<?php

namespace Codeages\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Codeages\PluginBundle\System\PluginInstaller;
use Topxia\Common\BlockToolkit;

class PluginInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('plugin:install')
            ->setDescription('...')
            ->addArgument('code', InputArgument::REQUIRED, 'Plugin code.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $biz = $this->getContainer()->get('biz');

        $output->writeln(sprintf('Install plugin <comment>%s</comment> :', $code));

        $rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));
        if (empty($rootDir)) {
            throw new \RuntimeException('Plugin base directory is not exist.');
        }

        $installer = new PluginInstaller($rootDir, 'plugins', $biz);

        $output->write("  - Parse meta file plugin.json");
        $metas = $installer->parseMetas($code);
        $output->writeln("  <info>[Ok]</info>");

        $output->write("  - Execute create database scripts.");
        $executed = $installer->executeDatabaseScript($code);
        $output->writeln($executed ? "  <info>[Ok]</info>" : "  <info>[Ignore]</info>");

        $output->write("  - Execute install script.");
        $executed = $installer->executeScript($code);
        $output->writeln($executed ? "  <info>[Ok]</info>" : "  <info>[Ignore]</info>");

        $output->write("  - Install assets.");
        $content = $installer->installAssets($code);
        $output->writeln("  <info>[Ok]</info>");
        $output->writeln($content);

        $output->write("  - Install block.");
        BlockToolkit::init($installer->getPluginDirectory($code).'/block.json', $this->getContainer());
        $output->writeln("  <info>[Ok]</info>");

        $output->write("  - Create plugin installed record.");
        $app = $installer->savePluginInstalledInfo($code);
        $output->writeln($app ? "  <info>[Ok]</info>" : "  <info>[Ignore]</info>");

        $output->write("  - Refresh plugin cache.");
        $installer->refreshInstalledPluginConfiguration();
        $output->writeln($executed ? "  <info>[Ok]</info>" : "  <info>[Ignore]</info>");

        $output->writeln("<info>Install successed!</info>\n");

    }

}
