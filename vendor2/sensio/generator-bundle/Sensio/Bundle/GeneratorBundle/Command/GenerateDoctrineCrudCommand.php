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

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator;
use Sensio\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;

/**
 * Generates a CRUD for a Doctrine entity.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineCrudCommand extends GenerateDoctrineCommand
{
    private $formGenerator;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::OPTIONAL, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputOption('route-prefix', '', InputOption::VALUE_REQUIRED, 'The route prefix'),
                new InputOption('with-write', '', InputOption::VALUE_NONE, 'Whether or not to generate create, new and delete actions'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation'),
                new InputOption('overwrite', '', InputOption::VALUE_NONE, 'Do not stop the generation if crud controller already exist, thus overwriting all generated files'),
            ))
            ->setDescription('Generates a CRUD based on a Doctrine entity')
            ->setHelp(<<<EOT
The <info>doctrine:generate:crud</info> command generates a CRUD based on a Doctrine entity.

The default command only generates the list and show actions.

<info>php app/console doctrine:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin</info>

Using the --with-write option allows to generate the new, edit and delete actions.

<info>php app/console doctrine:generate:crud --entity=AcmeBlogBundle:Post --route-prefix=post_admin --with-write</info>

Every generated file is based on a template. There are default templates but they can be overriden by placing custom templates in one of the following locations, by order of priority:

<info>BUNDLE_PATH/Resources/SensioGeneratorBundle/skeleton/crud
APP_PATH/Resources/SensioGeneratorBundle/skeleton/crud</info>

And

<info>__bundle_path__/Resources/SensioGeneratorBundle/skeleton/form
__project_root__/app/Resources/SensioGeneratorBundle/skeleton/form</info>

You can check https://github.com/sensio/SensioGeneratorBundle/tree/master/Resources/skeleton
in order to know the file structure of the skeleton
EOT
            )
            ->setName('doctrine:generate:crud')
            ->setAliases(array('generate:doctrine:crud'))
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $format = Validators::validateFormat($input->getOption('format'));
        $prefix = $this->getRoutePrefix($input, $entity);
        $withWrite = $input->getOption('with-write');
        $forceOverwrite = $input->getOption('overwrite');

        $dialog->writeSection($output, 'CRUD generation');

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $metadata    = $this->getEntityMetadata($entityClass);
        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $entity, $metadata[0], $format, $prefix, $withWrite, $forceOverwrite);

        $output->writeln('Generating the CRUD code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // form
        if ($withWrite) {
            $output->write('Generating the Form code: ');
            if ($this->generateForm($bundle, $entity, $metadata)) {
                $output->writeln('<info>OK</info>');
            } else {
                $output->writeln('<warning>Already exists, skipping</warning>');
            }
        }

        // routing
        if ('annotation' != $format) {
            $runner($this->updateRouting($dialog, $input, $output, $bundle, $format, $entity, $prefix));
        }

        $dialog->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Doctrine2 CRUD generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, you need to give the entity for which you want to generate a CRUD.',
            'You can give an entity that does not exist yet and the wizard will help',
            'you defining it.',
            '',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));

        if ($input->hasArgument('entity') && $input->getArgument('entity') != '') {
            $input->setOption('entity', $input->getArgument('entity'));
        }

        $entity = $dialog->askAndValidate($output, $dialog->getQuestion('The Entity shortcut name', $input->getOption('entity')), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'), false, $input->getOption('entity'));
        $input->setOption('entity', $entity);
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        // write?
        $withWrite = $input->getOption('with-write') ?: false;
        $output->writeln(array(
            '',
            'By default, the generator creates two actions: list and show.',
            'You can also ask it to generate "write" actions: new, update, and delete.',
            '',
        ));
        $withWrite = $dialog->askConfirmation($output, $dialog->getQuestion('Do you want to generate the "write" actions', $withWrite ? 'yes' : 'no', '?'), $withWrite);
        $input->setOption('with-write', $withWrite);

        // format
        $format = $input->getOption('format');
        $output->writeln(array(
            '',
            'Determine the format to use for the generated CRUD.',
            '',
        ));
        $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml, php, or annotation)', $format), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'), false, $format);
        $input->setOption('format', $format);

        // route prefix
        $prefix = $this->getRoutePrefix($input, $entity);
        $output->writeln(array(
            '',
            'Determine the routes prefix (all the routes will be "mounted" under this',
            'prefix: /prefix/, /prefix/new, ...).',
            '',
        ));
        $prefix = $dialog->ask($output, $dialog->getQuestion('Routes prefix', '/'.$prefix), '/'.$prefix);
        $input->setOption('route-prefix', $prefix);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a CRUD controller for \"<info>%s:%s</info>\"", $bundle, $entity),
            sprintf("using the \"<info>%s</info>\" format.", $format),
            '',
        ));
    }

    /**
     * Tries to generate forms if they don't exist yet and if we need write operations on entities.
     */
    protected function generateForm($bundle, $entity, $metadata)
    {
        try {
            $this->getFormGenerator($bundle)->generate($bundle, $entity, $metadata[0]);
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    protected function updateRouting(DialogHelper $dialog, InputInterface $input, OutputInterface $output, BundleInterface $bundle, $format, $entity, $prefix)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $auto = $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of the Routing', 'yes', '?'), true);
        }

        $output->write('Importing the CRUD routes: ');
        $this->getContainer()->get('filesystem')->mkdir($bundle->getPath().'/Resources/config/');
        $routing = new RoutingManipulator($bundle->getPath().'/Resources/config/routing.yml');
        try {
            $ret = $auto ? $routing->addResource($bundle->getName(), $format, '/'.$prefix, 'routing/'.strtolower(str_replace('\\', '_', $entity))) : false;
        } catch (\RuntimeException $exc) {
            $ret = false;
        }

        if (!$ret) {
            $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing/%s.%s\"</comment>\n", $bundle->getName(), strtolower(str_replace('\\', '_', $entity)), $format);
            $help .= sprintf("        <comment>prefix:   /%s</comment>\n", $prefix);

            return array(
                '- Import the bundle\'s routing resource in the bundle routing file',
                sprintf('  (%s).', $bundle->getPath().'/Resources/config/routing.yml'),
                '',
                sprintf('    <comment>%s:</comment>', $bundle->getName().('' !== $prefix ? '_'.str_replace('/', '_', $prefix) : '')),
                $help,
                '',
            );
        }
    }

    protected function getRoutePrefix(InputInterface $input, $entity)
    {
        $prefix = $input->getOption('route-prefix') ?: strtolower(str_replace(array('\\', '/'), '_', $entity));

        if ($prefix && '/' === $prefix[0]) {
            $prefix = substr($prefix, 1);
        }

        return $prefix;
    }

    protected function createGenerator($bundle = null)
    {
        return new DoctrineCrudGenerator($this->getContainer()->get('filesystem'));
    }

    protected function getFormGenerator($bundle = null)
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new DoctrineFormGenerator($this->getContainer()->get('filesystem'));
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }

    public function setFormGenerator(DoctrineFormGenerator $formGenerator)
    {
        $this->formGenerator = $formGenerator;
    }
}
