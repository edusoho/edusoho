<?php

namespace Bazinga\Bundle\JsTranslationBundle\Command;

use Bazinga\Bundle\JsTranslationBundle\Dumper\TranslationDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Adrien Russo <adrien.russo.qc@gmail.com>
 * @author Hugo Monteiro <hugo.monteiro@gmail.com>
 */
class DumpCommand extends Command
{
    /**
     * @var TranslationDumper
     */
    private $dumper;

    /**
     * @var string
     */
    private $kernelRootDir;

    /**
     * @var string
     */
    private $targetPath;

    /**
     * @param TranslationDumper $dumper
     * @param $kernelRootDir
     */
    public function __construct(TranslationDumper $dumper, $kernelRootDir)
    {
        $this->dumper = $dumper;
        $this->kernelRootDir = $kernelRootDir;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('bazinga:js-translation:dump')
            ->setDefinition(array(
                new InputArgument(
                    'target',
                    InputArgument::OPTIONAL,
                    'Override the target directory to dump JS translation files in.'
                ),
            ))
            ->setDescription('Dumps all JS translation files to the filesystem')
            ->addOption(
                'pattern',
                null,
                InputOption::VALUE_REQUIRED,
                'The route pattern: e.g. "/translations/{domain}.{_format}"',
                TranslationDumper::DEFAULT_TRANSLATION_PATTERN
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'If set, only the passed formats will be generated',
                array()
            )
            ->addOption(
                'merge-domains',
                null,
                InputOption::VALUE_NONE,
                'If set, all domains will be merged into a single file per language'
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->targetPath = $input->getArgument('target') ?: sprintf('%s/../web/js', $this->kernelRootDir);
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $formats = $input->getOption('format');
        $merge = (object) array(
            'domains' => $input->getOption('merge-domains')
        );

        if (!is_dir($dir = dirname($this->targetPath))) {
            $output->writeln('<info>[dir+]</info>  ' . $dir);
            if (false === @mkdir($dir, 0777, true)) {
                throw new \RuntimeException('Unable to create directory ' . $dir);
            }
        }

        $output->writeln(sprintf(
            'Installing translation files in <comment>%s</comment> directory',
            $this->targetPath
        ));

        $this->dumper->dump($this->targetPath, $input->getOption('pattern'), $formats, $merge);
    }
}
