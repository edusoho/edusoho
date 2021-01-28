<?php

namespace Codeages\PluginBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Codeages\PluginBundle\System\PluginRegister;
use AppBundle\Common\BlockToolkit;

class PluginRegisterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('plugin:register')
            ->addArgument('code', InputArgument::REQUIRED, 'Plugin code.')
            ->addOption('without-database', null, InputOption::VALUE_NONE, 'create database?')
            ->setDescription('Register plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $biz = $this->getContainer()->get('biz');
        $code = $input->getArgument('code');
        $withoutDatabase = $input->getOption('without-database');

        $output->writeln(sprintf('Register plugin <comment>%s</comment> :', $code));

        $rootDir = dirname($this->getContainer()->getParameter('kernel.root_dir'));

        $installer = new PluginRegister($rootDir, 'plugins', $biz);

        if ($installer->isPluginRegisted($code)) {
            throw new \RuntimeException('Plugin is already registed.');
        }

        $output->write('  - Parse meta file plugin.json');
        $metas = $installer->parseMetas($code);
        $output->writeln('  <info>[Ok]</info>');

        if (!$withoutDatabase) {
            $output->write('  - Execute create database scripts.');
            $executed = $installer->executeDatabaseScript($code);
            $output->writeln($executed ? '  <info>[Ok]</info>' : '  <info>[Ignore]</info>');
        }

        $output->write('  - Execute install script.');
        $executed = $installer->executeScript($code);
        $output->writeln($executed ? '  <info>[Ok]</info>' : '  <info>[Ignore]</info>');

        $output->write('  - Install assets.');
        $content = $installer->installAssets($code);
        $output->writeln('  <info>[Ok]</info>');
        $output->writeln($content);

        $output->write('  - Install block.');
        BlockToolkit::init($installer->getPluginDirectory($code).'/block.json', $this->getContainer());
        $output->writeln('  <info>[Ok]</info>');

        $output->write('  - Create plugin installed record.');
        $app = $installer->registerPlugin($code);
        $output->writeln($app ? '  <info>[Ok]</info>' : '  <info>[Ignore]</info>');

        $output->write('  - Refresh plugin cache.');
        $installer->refreshInstalledPluginConfiguration();
        $output->writeln($executed ? '  <info>[Ok]</info>' : '  <info>[Ignore]</info>');

        $output->writeln("<info>Finished!</info>\n");
    }
}
