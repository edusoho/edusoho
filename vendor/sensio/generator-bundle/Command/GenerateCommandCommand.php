<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sensio\Bundle\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Sensio\Bundle\GeneratorBundle\Generator\CommandGenerator;

/**
 * Generates commands.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class GenerateCommandCommand extends GeneratorCommand
{
    const MAX_ATTEMPTS = 5;

    /**
     * @see Command
     */
    public function configure()
    {
        $this
            ->setName('generate:command')
            ->setDescription('Generates a console command')
            ->setDefinition(array(
                new InputArgument('bundle', InputArgument::OPTIONAL, 'The bundle where the command is generated'),
                new InputArgument('name', InputArgument::OPTIONAL, 'The command\'s name (e.g. app:my-command)'),
            ))
            ->setHelp(<<<EOT
The <info>generate:command</info> command helps you generate new commands
inside bundles. Provide the bundle name as the first argument and the command
name as the second argument:

<info>php app/console generate:command AppBundle blog:publish-posts</info>

If any of the arguments is missing, the command will ask for their values
interactively. If you want to disable any user interaction, use
<comment>--no-interaction</comment>, but don't forget to pass all needed arguments.

Every generated file is based on a template. There are default templates but they can
be overridden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/command
APP_PATH/Resources/SensioGeneratorBundle/skeleton/command</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton.
EOT
            )
        ;
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $bundle = $input->getArgument('bundle');
        $name = $input->getArgument('name');

        if (null !== $bundle && null !== $name) {
            return;
        }

        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Symfony command generator');

        // bundle
        if (null !== $bundle) {
            $output->writeln(sprintf('Bundle name: %s', $bundle));
        } else {
            $output->writeln(array(
                '',
                'First, you need to give the name of the bundle where the command will',
                'be generated (e.g. <comment>AppBundle</comment>)',
                '',
            ));

            $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

            $question = new Question($questionHelper->getQuestion('Bundle name', $bundle), $bundle);
            $question->setAutocompleterValues($bundleNames);
            $question->setValidator(function ($answer) use ($bundleNames) {
                if (!in_array($answer, $bundleNames)) {
                    throw new \RuntimeException(sprintf('Bundle "%s" does not exist.', $answer));
                }

                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);

            $bundle = $questionHelper->ask($input, $output, $question);
            $input->setArgument('bundle', $bundle);
        }

        // command name
        if (null !== $name) {
            $output->writeln(sprintf('Command name: %s', $name));
        } else {
            $output->writeln(array(
                '',
                'Now, provide the name of the command as you type it in the console',
                '(e.g. <comment>app:my-command</comment>)',
                '',
            ));

            $question = new Question($questionHelper->getQuestion('Command name', $name), $name);
            $question->setValidator(function ($answer) {
                if (empty($answer)) {
                   throw new \RuntimeException('The command name cannot be empty.');
                }

                return $answer;
            });
            $question->setMaxAttempts(self::MAX_ATTEMPTS);

            $name = $questionHelper->ask($input, $output, $question);
            $input->setArgument('name', $name);
        }

        // summary and confirmation
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg-white', true),
            '',
            sprintf('You are going to generate a <info>%s</info> command inside <info>%s</info> bundle.', $name, $bundle),
        ));

        $question = new Question($questionHelper->getQuestion('Do you confirm generation', 'yes', '?'), true);
        if (!$questionHelper->ask($input, $output, $question)) {
            $output->writeln('<error>Command aborted</error>');

            return 1;
        }
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $bundle = $input->getArgument('bundle');
        $name = $input->getArgument('name');

        try {
            $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
        }

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $name);

        $output->writeln(sprintf('Generated the <info>%s</info> command in <info>%s</info>', $name, $bundle->getName()));
        $questionHelper->writeGeneratorSummary($output, array());
    }

    protected function createGenerator()
    {
        return new CommandGenerator($this->getContainer()->get('filesystem'));
    }
}
