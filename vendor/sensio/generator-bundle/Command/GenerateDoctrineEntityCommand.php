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

use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Console\Question\Question;
use Doctrine\DBAL\Types\Type;

/**
 * Initializes a Doctrine entity inside a bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineEntityCommand extends GenerateDoctrineCommand
{
    protected function configure()
    {
        $this
            ->setName('doctrine:generate:entity')
            ->setAliases(array('generate:doctrine:entity'))
            ->setDescription('Generates a new Doctrine entity inside a bundle')
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'The fields to create with the new entity')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, yml, or annotation)', 'annotation')
            ->setHelp(<<<EOT
The <info>%command.name%</info> task generates a new Doctrine
entity inside a bundle:

<info>php %command.full_name% --entity=AcmeBlogBundle:Blog/Post</info>

The above command would initialize a new entity in the following entity
namespace <info>Acme\BlogBundle\Entity\Blog\Post</info>.

You can also optionally specify the fields you want to generate in the new
entity:

<info>php %command.full_name% --entity=AcmeBlogBundle:Blog/Post --fields="title:string(255) body:text"</info>

By default, the command uses annotations for the mapping information; change it
with <comment>--format</comment>:

<info>php %command.full_name% --entity=AcmeBlogBundle:Blog/Post --format=yml</info>

To deactivate the interaction mode, simply use the <comment>--no-interaction</comment> option
without forgetting to pass all needed options:

<info>php %command.full_name% --entity=AcmeBlogBundle:Blog/Post --format=annotation --fields="title:string(255) body:text" --no-interaction</info>

This also has support for passing field specific attributes:

<info>php %command.full_name% --entity=AcmeBlogBundle:Blog/Post --format=annotation --fields="title:string(length=255 nullable=true unique=true) body:text ranking:decimal(precision:10 scale:0)" --no-interaction</info>
EOT
        );
    }

    /**
     * @throws \InvalidArgumentException When the bundle doesn't end with Bundle (Example: "Bundle/MySampleBundle")
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);
        $format = Validators::validateFormat($input->getOption('format'));
        $fields = $this->parseFields($input->getOption('fields'));

        $questionHelper->writeSection($output, 'Entity generation');

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        /** @var DoctrineEntityGenerator $generator */
        $generator = $this->getGenerator();
        $generatorResult = $generator->generate($bundle, $entity, $format, array_values($fields));

        $output->writeln(sprintf(
            '> Generating entity class <info>%s</info>: <comment>OK!</comment>',
            $this->makePathRelative($generatorResult->getEntityPath())
        ));
        $output->writeln(sprintf(
            '> Generating repository class <info>%s</info>: <comment>OK!</comment>',
            $this->makePathRelative($generatorResult->getRepositoryPath())
        ));
        if ($generatorResult->getMappingPath()) {
            $output->writeln(sprintf(
                '> Generating mapping file <info>%s</info>: <comment>OK!</comment>',
                $this->makePathRelative($generatorResult->getMappingPath())
            ));
        }

        $questionHelper->writeGeneratorSummary($output, array());
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Doctrine2 entity generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate Doctrine2 entities.',
            '',
            'First, you need to give the entity name you want to generate.',
            'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
            '',
        ));

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        while (true) {
            $question = new Question($questionHelper->getQuestion('The Entity shortcut name', $input->getOption('entity')), $input->getOption('entity'));
            $question->setValidator(array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'));
            $question->setAutocompleterValues($bundleNames);
            $entity = $questionHelper->ask($input, $output, $question);

            list($bundle, $entity) = $this->parseShortcutNotation($entity);

            // check reserved words
            if ($this->getGenerator()->isReservedKeyword($entity)) {
                $output->writeln(sprintf('<bg=red> "%s" is a reserved word</>.', $entity));
                continue;
            }

            try {
                $b = $this->getContainer()->get('kernel')->getBundle($bundle);

                if (!file_exists($b->getPath().'/Entity/'.str_replace('\\', '/', $entity).'.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Entity "%s:%s" already exists</>.', $bundle, $entity));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }
        $input->setOption('entity', $bundle.':'.$entity);

        // format
        $output->writeln(array(
            '',
            'Determine the format to use for the mapping information.',
            '',
        ));

        $formats = array('yml', 'xml', 'php', 'annotation');

        $question = new Question($questionHelper->getQuestion('Configuration format (yml, xml, php, or annotation)', $input->getOption('format')), $input->getOption('format'));
        $question->setValidator(array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'));
        $question->setAutocompleterValues($formats);
        $format = $questionHelper->ask($input, $output, $question);
        $input->setOption('format', $format);

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $questionHelper));
    }

    private function parseFields($input)
    {
        if (is_array($input)) {
            return $input;
        }

        $fields = array();
        foreach (preg_split('{(?:\([^\(]*\))(*SKIP)(*F)|\s+}', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            $fieldAttributes = array();
            if (strlen($name)) {
                $fieldAttributes['fieldName'] = $name;
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('{(.*)\((.*)\)}', $type, $matches);
                $fieldAttributes['type'] = isset($matches[1][0]) ? $matches[1][0] : $type;
                $length = null;
                if ('string' === $fieldAttributes['type']) {
                    $fieldAttributes['length'] = $length;
                }
                if (isset($matches[2][0]) && $length = $matches[2][0]) {
                    $attributesFound = array();
                    if (false !== strpos($length, '=')) {
                        preg_match_all('{([^,= ]+)=([^,= ]+)}', $length, $result);
                        $attributesFound = array_combine($result[1], $result[2]);
                    } else {
                        $fieldAttributes['length'] = $length;
                    }
                    $fieldAttributes = array_merge($fieldAttributes, $attributesFound);
                    foreach (array('length', 'precision', 'scale') as $intAttribute) {
                        if (isset($fieldAttributes[$intAttribute])) {
                            $fieldAttributes[$intAttribute] = (int) $fieldAttributes[$intAttribute];
                        }
                    }
                    foreach (array('nullable', 'unique') as $boolAttribute) {
                        if (isset($fieldAttributes[$boolAttribute])) {
                            $fieldAttributes[$boolAttribute] = filter_var($fieldAttributes[$boolAttribute], FILTER_VALIDATE_BOOLEAN);
                        }
                    }
                }

                $fields[$name] = $fieldAttributes;
            }
        }

        return $fields;
    }

    private function addFields(InputInterface $input, OutputInterface $output, QuestionHelper $questionHelper)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln(array(
            '',
            'Instead of starting with a blank entity, you can add some fields now.',
            'Note that the primary key will be added automatically (named <comment>id</comment>).',
            '',
        ));
        $output->write('<info>Available types:</info> ');

        $types = array_keys(Type::getTypesMap());
        $count = 20;
        foreach ($types as $i => $type) {
            if ($count > 50) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen($type);
            $output->write(sprintf('<comment>%s</comment>', $type));
            if (count($types) != $i + 1) {
                $output->write(', ');
            } else {
                $output->write('.');
            }
        }
        $output->writeln('');

        $fieldValidator = function ($type) use ($types) {
            if (!in_array($type, $types)) {
                throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
            }

            return $type;
        };

        $lengthValidator = function ($length) {
            if (!$length) {
                return $length;
            }

            $result = filter_var($length, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 1),
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid length "%s".', $length));
            }

            return $length;
        };

        $boolValidator = function ($value) {
            if (null === $valueAsBool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
                throw new \InvalidArgumentException(sprintf('Invalid bool value "%s".', $value));
            }

            return $valueAsBool;
        };

        $precisionValidator = function ($precision) {
            if (!$precision) {
                return $precision;
            }

            $result = filter_var($precision, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 1, 'max_range' => 65),
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid precision "%s".', $precision));
            }

            return $precision;
        };

        $scaleValidator = function ($scale) {
            if (!$scale) {
                return $scale;
            }

            $result = filter_var($scale, FILTER_VALIDATE_INT, array(
                'options' => array('min_range' => 0, 'max_range' => 30),
            ));

            if (false === $result) {
                throw new \InvalidArgumentException(sprintf('Invalid scale "%s".', $scale));
            }

            return $scale;
        };

        while (true) {
            $output->writeln('');
            $generator = $this->getGenerator();
            $question = new Question($questionHelper->getQuestion('New field name (press <return> to stop adding fields)', null), null);
            $question->setValidator(function ($name) use ($fields, $generator) {
                if (isset($fields[$name]) || 'id' == $name) {
                    throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
                }

                // check reserved words
                if ($generator->isReservedKeyword($name)) {
                    throw new \InvalidArgumentException(sprintf('Name "%s" is a reserved word.', $name));
                }

                // check for valid PHP variable name
                if (!is_null($name) && !$generator->isValidPhpVariableName($name)) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not a valid PHP variable name.', $name));
                }

                return $name;
            });

            $columnName = $questionHelper->ask($input, $output, $question);
            if (!$columnName) {
                break;
            }

            $defaultType = 'string';

            // try to guess the type by the column name prefix/suffix
            if (substr($columnName, -3) == '_at') {
                $defaultType = 'datetime';
            } elseif (substr($columnName, -3) == '_id') {
                $defaultType = 'integer';
            } elseif (substr($columnName, 0, 3) == 'is_') {
                $defaultType = 'boolean';
            } elseif (substr($columnName, 0, 4) == 'has_') {
                $defaultType = 'boolean';
            }

            $question = new Question($questionHelper->getQuestion('Field type', $defaultType), $defaultType);
            $question->setValidator($fieldValidator);
            $question->setAutocompleterValues($types);
            $type = $questionHelper->ask($input, $output, $question);

            $data = array('columnName' => $columnName, 'fieldName' => lcfirst(Container::camelize($columnName)), 'type' => $type);

            if ($type == 'string') {
                $question = new Question($questionHelper->getQuestion('Field length', 255), 255);
                $question->setValidator($lengthValidator);
                $data['length'] = $questionHelper->ask($input, $output, $question);
            } elseif ('decimal' === $type) {
                // 10 is the default value given in \Doctrine\DBAL\Schema\Column::$_precision
                $question = new Question($questionHelper->getQuestion('Precision', 10), 10);
                $question->setValidator($precisionValidator);
                $data['precision'] = $questionHelper->ask($input, $output, $question);

                // 0 is the default value given in \Doctrine\DBAL\Schema\Column::$_scale
                $question = new Question($questionHelper->getQuestion('Scale', 0), 0);
                $question->setValidator($scaleValidator);
                $data['scale'] = $questionHelper->ask($input, $output, $question);
            }

            $question = new Question($questionHelper->getQuestion('Is nullable', 'false'), false);
            $question->setValidator($boolValidator);
            $question->setAutocompleterValues(array('true', 'false'));
            if ($nullable = $questionHelper->ask($input, $output, $question)) {
                $data['nullable'] = $nullable;
            }

            $question = new Question($questionHelper->getQuestion('Unique', 'false'), false);
            $question->setValidator($boolValidator);
            $question->setAutocompleterValues(array('true', 'false'));
            if ($unique = $questionHelper->ask($input, $output, $question)) {
                $data['unique'] = $unique;
            }

            $fields[$columnName] = $data;
        }

        return $fields;
    }

    protected function createGenerator()
    {
        return new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
    }
}
