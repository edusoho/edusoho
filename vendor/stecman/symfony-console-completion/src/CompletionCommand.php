<?php

namespace Stecman\Component\Symfony\Console\BashCompletion;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CompletionCommand extends SymfonyCommand
{

    /**
     * @var CompletionHandler
     */
    protected $handler;

    protected function configure()
    {
        $this
            ->setName('_completion')
            ->setDefinition($this->createDefinition())
            ->setDescription('BASH completion hook.')
            ->setHelp(<<<END
To enable BASH completion, run:

    <comment>eval `[program] _completion -g`</comment>.

Or for an alias:

    <comment>eval `[program] _completion -g -p [alias]`</comment>.

END
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getNativeDefinition()
    {
        return $this->createDefinition();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handler = new CompletionHandler($this->getApplication());
        $handler = $this->handler;

        if ($input->getOption('generate-hook')) {
            global $argv;
            $program = $argv[0];

            $factory = new HookFactory();
            $alias = $input->getOption('program');
            $multiple = (bool)$input->getOption('multiple');

            // When completing for multiple apps having absolute path in the alias doesn't make sense.
            if (!$alias && $multiple) {
                $alias = basename($program);
            }

            $hook = $factory->generateHook(
                $input->getOption('shell-type') ?: $this->getShellType(),
                $program,
                $alias,
                $multiple
            );

            $output->write($hook, true);
        } else {
            $handler->setContext(new EnvironmentCompletionContext());
            $output->write($this->runCompletion(), true);
        }
    }

    /**
     * Run the completion handler and return a filtered list of results
     *
     * @deprecated - This will be removed in 1.0.0 in favour of CompletionCommand::configureCompletion
     *
     * @return string[]
     */
    protected function runCompletion()
    {
        $this->configureCompletion($this->handler);
        return $this->handler->runCompletion();
    }

    /**
     * Configure the CompletionHandler instance before it is run
     *
     * @param CompletionHandler $handler
     */
    protected function configureCompletion(CompletionHandler $handler)
    {
        // Override this method to configure custom value completions
    }

    /**
     * Determine the shell type for use with HookFactory
     *
     * @return string
     */
    protected function getShellType()
    {
        if (!getenv('SHELL')) {
            throw new \RuntimeException('Could not read SHELL environment variable. Please specify your shell type using the --shell-type option.');
        }

        return basename(getenv('SHELL'));
    }

    protected function createDefinition()
    {
        return new InputDefinition(array(
            new InputOption(
                'generate-hook',
                'g',
                InputOption::VALUE_NONE,
                'Generate BASH code that sets up completion for this application.'
            ),
            new InputOption(
                'program',
                'p',
                InputOption::VALUE_REQUIRED,
                "Program name that should trigger completion\n<comment>(defaults to the absolute application path)</comment>."
            ),
            new InputOption(
                'multiple',
                'm',
                InputOption::VALUE_NONE,
                "Generated hook can be used for multiple applications."
            ),
            new InputOption(
                'shell-type',
                null,
                InputOption::VALUE_OPTIONAL,
                'Set the shell type (zsh or bash). Otherwise this is determined automatically.'
            ),
        ));
    }
}
