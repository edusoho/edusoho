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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Sensio\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;

/**
 * Generates bundles.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateBundleCommand extends GeneratorCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the bundle to create'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the bundle'),
                new InputOption('bundle-name', '', InputOption::VALUE_REQUIRED, 'The optional bundle name'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)'),
                new InputOption('structure', '', InputOption::VALUE_NONE, 'Whether to generate the whole directory structure'),
            ))
            ->setDescription('Generates a bundle')
            ->setHelp(<<<EOT
The <info>generate:bundle</info> command helps you generates new bundles.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--namespace</comment> is the only one needed if you follow the
conventions):

<info>php app/console generate:bundle --namespace=Acme/BlogBundle</info>

Note that you can use <comment>/</comment> instead of <comment>\\ </comment>for the namespace delimiter to avoid any
problem.

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options:

<info>php app/console generate:bundle --namespace=Acme/BlogBundle --dir=src [--bundle-name=...] --no-interaction</info>

Note that the bundle namespace must end with "Bundle".
EOT
            )
            ->setName('generate:bundle')
        ;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
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

        foreach (array('namespace', 'dir') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        // validate the namespace, but don't require a vendor namespace
        $namespace = Validators::validateBundleNamespace($input->getOption('namespace'), false);
        if (!$bundle = $input->getOption('bundle-name')) {
            $bundle = strtr($namespace, array('\\' => ''));
        }
        $bundle = Validators::validateBundleName($bundle);
        $dir = Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace);
        if (null === $input->getOption('format')) {
            $input->setOption('format', 'annotation');
        }
        $format = Validators::validateFormat($input->getOption('format'));
        $structure = $input->getOption('structure');

        $dialog->writeSection($output, 'Bundle generation');

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $generator = $this->getGenerator();
        $generator->generate($namespace, $bundle, $dir, $format, $structure);

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $errors = array();
        $runner = $dialog->getRunner($output, $errors);

        // check that the namespace is already autoloaded
        $runner($this->checkAutoloader($output, $namespace, $bundle, $dir));

        // register the bundle in the Kernel class
        $runner($this->updateKernel($dialog, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        // routing
        $runner($this->updateRouting($dialog, $input, $output, $bundle, $format));

        $dialog->writeGeneratorSummary($output, $errors);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Symfony2 bundle generator');

        // namespace
        $namespace = null;
        try {
            // validate the namespace option (if any) but don't require the vendor namespace
            $namespace = $input->getOption('namespace') ? Validators::validateBundleNamespace($input->getOption('namespace'), false) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $namespace) {
            $output->writeln(array(
                '',
                'Your application code must be written in <comment>bundles</comment>. This command helps',
                'you generate them easily.',
                '',
                'Each bundle is hosted under a namespace (like <comment>Acme/Bundle/BlogBundle</comment>).',
                'The namespace should begin with a "vendor" name like your company name, your',
                'project name, or your client name, followed by one or more optional category',
                'sub-namespaces, and it should end with the bundle name itself',
                '(which must have <comment>Bundle</comment> as a suffix).',
                '',
                'See http://symfony.com/doc/current/cookbook/bundles/best_practices.html#index-1 for more',
                'details on bundle naming conventions.',
                '',
                'Use <comment>/</comment> instead of <comment>\\ </comment> for the namespace delimiter to avoid any problem.',
                '',
            ));

            $acceptedNamespace = false;
            while (!$acceptedNamespace) {
                $namespace = $dialog->askAndValidate(
                    $output,
                    $dialog->getQuestion('Bundle namespace', $input->getOption('namespace')),
                    function ($namespace) use ($dialog, $output) {
                        // validate it, but don't require the vendor namespace
                        return Validators::validateBundleNamespace($namespace, false);
                    },
                    false,
                    $input->getOption('namespace')
                );

                // mark as accepted, unless they want to try again below
                $acceptedNamespace = true;

                // see if there is a vendor namespace. If not, this could be accidental
                if (false === strpos($namespace, '\\')) {
                    // language is (almost) duplicated in Validators
                    $msg = array();
                    $msg[] = '';
                    $msg[] = sprintf('The namespace sometimes contain a vendor namespace (e.g. <info>VendorName/BlogBundle</info> instead of simply <info>%s</info>).', $namespace, $namespace);
                    $msg[] = 'If you\'ve *did* type a vendor namespace, try using a forward slash <info>/</info> (<info>Acme/BlogBundle</info>)?';
                    $msg[] = '';
                    $output->writeln($msg);

                    $acceptedNamespace = $dialog->askConfirmation(
                        $output,
                        $dialog->getQuestion(
                            sprintf('Keep <comment>%s</comment> as the bundle namespace (choose no to try again)?', $namespace),
                            'yes'
                        )
                    );
                }
            }
            $input->setOption('namespace', $namespace);
        }

        // bundle name
        $bundle = null;
        try {
            $bundle = $input->getOption('bundle-name') ? Validators::validateBundleName($input->getOption('bundle-name')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $bundle) {
            $bundle = strtr($namespace, array('\\Bundle\\' => '', '\\' => ''));

            $output->writeln(array(
                '',
                'In your code, a bundle is often referenced by its name. It can be the',
                'concatenation of all namespace parts but it\'s really up to you to come',
                'up with a unique name (a good practice is to start with the vendor name).',
                'Based on the namespace, we suggest <comment>'.$bundle.'</comment>.',
                '',
            ));
            $bundle = $dialog->askAndValidate($output, $dialog->getQuestion('Bundle name', $bundle), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateBundleName'), false, $bundle);
            $input->setOption('bundle-name', $bundle);
        }

        // target dir
        $dir = null;
        try {
            $dir = $input->getOption('dir') ? Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $dir) {
            $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $output->writeln(array(
                '',
                'The bundle can be generated anywhere. The suggested default directory uses',
                'the standard conventions.',
                '',
            ));
            $dir = $dialog->askAndValidate($output, $dialog->getQuestion('Target directory', $dir), function ($dir) use ($bundle, $namespace) { return Validators::validateTargetDir($dir, $bundle, $namespace); }, false, $dir);
            $input->setOption('dir', $dir);
        }

        // format
        $format = null;
        try {
            $format = $input->getOption('format') ? Validators::validateFormat($input->getOption('format')) : null;
        } catch (\Exception $error) {
            $output->writeln($dialog->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $format) {
            $output->writeln(array(
                '',
                'Determine the format to use for the generated configuration.',
                '',
            ));
            $format = $dialog->askAndValidate($output, $dialog->getQuestion('Configuration format (yml, xml, php, or annotation)', $input->getOption('format')), array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'), false, $input->getOption('format'));
            $input->setOption('format', $format);
        }

        // optional files to generate
        $output->writeln(array(
            '',
            'To help you get started faster, the command can generate some',
            'code snippets for you.',
            '',
        ));

        $structure = $input->getOption('structure');
        if (!$structure && $dialog->askConfirmation($output, $dialog->getQuestion('Do you want to generate the whole directory structure', 'no', '?'), false)) {
            $structure = true;
        }
        $input->setOption('structure', $structure);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s\\%s</info>\" bundle\nin \"<info>%s</info>\" using the \"<info>%s</info>\" format.", $namespace, $bundle, $dir, $format),
            '',
        ));
    }

    protected function checkAutoloader(OutputInterface $output, $namespace, $bundle, $dir)
    {
        $output->write('Checking that the bundle is autoloaded: ');
        if (!class_exists($namespace.'\\'.$bundle)) {
            return array(
                '- Edit the <comment>composer.json</comment> file and register the bundle',
                '  namespace in the "autoload" section:',
                '',
            );
        }
    }

    protected function updateKernel(DialogHelper $dialog, InputInterface $input, OutputInterface $output, KernelInterface $kernel, $namespace, $bundle)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $auto = $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of your Kernel', 'yes', '?'), true);
        }

        $output->write('Enabling the bundle inside the Kernel: ');
        $manip = new KernelManipulator($kernel);
        try {
            $ret = $auto ? $manip->addBundle($namespace.'\\'.$bundle) : false;

            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);

                return array(
                    sprintf('- Edit <comment>%s</comment>', $reflected->getFilename()),
                    '  and add the following bundle in the <comment>AppKernel::registerBundles()</comment> method:',
                    '',
                    sprintf('    <comment>new %s(),</comment>', $namespace.'\\'.$bundle),
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already defined in <comment>AppKernel::registerBundles()</comment>.', $namespace.'\\'.$bundle),
                '',
            );
        }
    }

    protected function updateRouting(DialogHelper $dialog, InputInterface $input, OutputInterface $output, $bundle, $format)
    {
        $auto = true;
        if ($input->isInteractive()) {
            $auto = $dialog->askConfirmation($output, $dialog->getQuestion('Confirm automatic update of the Routing', 'yes', '?'), true);
        }

        $output->write('Importing the bundle routing resource: ');
        $routing = new RoutingManipulator($this->getContainer()->getParameter('kernel.root_dir').'/config/routing.yml');
        try {
            $ret = $auto ? $routing->addResource($bundle, $format) : true;
            if (!$ret) {
                if ('annotation' === $format) {
                    $help = sprintf("        <comment>resource: \"@%s/Controller/\"</comment>\n        <comment>type:     annotation</comment>\n", $bundle);
                } else {
                    $help = sprintf("        <comment>resource: \"@%s/Resources/config/routing.%s\"</comment>\n", $bundle, $format);
                }
                $help .= "        <comment>prefix:   /</comment>\n";

                return array(
                    '- Import the bundle\'s routing resource in the app main routing file:',
                    '',
                    sprintf('    <comment>%s:</comment>', $bundle),
                    $help,
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already imported.', $bundle),
                '',
            );
        }
    }

    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'));
    }
}
