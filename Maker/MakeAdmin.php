<?php

namespace Admingenerator\GeneratorBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class MakeAdmin extends AbstractMaker
{

  const ACTIONS = [
      'New'     => ['views' => [
          'index',
          'form',
      ]],
      'List'    => ['views' => [
          'index',
          'results',
          'filters',
          'row',
      ]],
      'Excel'   => ['views' => []],
      'Edit'    => ['views' => [
          'index',
          'form',
      ]],
      'Show'    => ['views' => ['index']],
      'Actions' => ['views' => ['index']],
  ];

  const FORMS = ['New', 'Filters', 'Edit'];

  const ORMS = ['doctrine', 'doctrine_odm', 'propel'];

  public static function getCommandName(): string
  {
    return 'admin:generate-admin';
  }

  public static function getCommandDescription(): string
  {
      return 'Generate new admin pages given a model';
  }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
  {
    $command
        ->addArgument('namespace', InputArgument::OPTIONAL, 'The namespace to place the admin', 'App')
        ->addArgument('sf4', InputArgument::OPTIONAL, 'Whether to use the Symfony 4 directory structure or the old bundle structure', 'Yes')
        ->addArgument('orm', InputArgument::OPTIONAL, 'The orm to use (propel, doctrine, doctrine_odm)', 'doctrine')
        ->addArgument('model-name', InputArgument::OPTIONAL, 'Base model name for admin module, without namespace.', 'YourModel')
        ->addArgument('prefix', InputArgument::OPTIONAL, 'The generator prefix ([prefix]-generator.yml)', 'AdminYourModel')
        ->setHelp(<<<EOT
The <info>admin:generate-admin</info> command helps you to generate new admin pages for a given model.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction.

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options.
EOT
        );
  }

  public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
  {
    $io->section('Welcome to the Admingenerator');
    $question = new Question(
        $command->getDefinition()->getArgument('namespace')->getDescription(),
        $input->getArgument('namespace')
    );
    $question->setValidator(function ($namespace) {
      return self::validateNamespace($namespace);
    });
    $input->setArgument('namespace', $io->askQuestion($question));

    $question = new Question(
        $command->getDefinition()->getArgument('orm')->getDescription(),
        $input->getArgument('orm')
    );
    $question->setValidator(function ($orm) {
      if (!in_array($orm, self::ORMS)) {
        throw new \InvalidArgumentException('Use a valid generator.');
      }

      return $orm;
    });
    $question->setAutocompleterValues(self::ORMS);
    $input->setArgument('orm', $io->askQuestion($question));

    $question = new Question(
        $command->getDefinition()->getArgument('model-name')->getDescription(),
        $input->getArgument('model-name')
    );
    $question->setValidator(function ($modelName) {
      if (empty($modelName) || preg_match('#[^a-zA-Z0-9]#', $modelName)) {
        throw new \InvalidArgumentException('Model name should not contain any special characters nor spaces.');
      }

      return $modelName;
    });
    $input->setArgument('model-name', $io->askQuestion($question));

    $question = new Question(
        $command->getDefinition()->getArgument('prefix')->getDescription(),
        $input->getArgument('prefix')
    );
    $question->setValidator(function ($prefix) {
      if (!preg_match('/([a-z]+)/i', $prefix)) {
        throw new \RuntimeException('Prefix has to be a single word');
      }

      return $prefix;
    });
    $input->setArgument('prefix', $io->askQuestion($question));

    $question = new ConfirmationQuestion(
        $command->getDefinition()->getArgument('sf4')->getDescription(),
        $input->getArgument('sf4')
    );
    $input->setArgument('sf4', $io->askQuestion($question));

  }

  public function configureDependencies(DependencyBuilder $dependencies): void
  {

  }

  public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
  {
    $orm = $input->getArgument('orm');
    // Retrieve model folder depending on chosen Model Manager
    $modelFolder = '';
    switch ($orm) {
      case 'propel':
        $modelFolder = 'Model';
        break;
      case 'doctrine':
        $modelFolder = 'Entity';
        break;
      case 'doctrine_odm':
        $modelFolder = 'Document';
        break;
    }

    $namespace        = $input->getArgument('namespace');
    $namespaceParts   = explode('\\', $namespace);
    $prefix           = $input->getArgument('prefix');
    $bundledNamespace = count($namespaceParts) > 1;
    $bundleName       = strtr($namespace, array('\\' => ''));
    $parameters       = [
        'bundle'          => $bundleName,
        'bundleName'      => $bundledNamespace ? end($namespaceParts) : $namespace,
        'generator'       => sprintf('admingenerator.generator.%s', $orm),
        'modelFolder'     => $modelFolder,
        'modelName'       => $input->getArgument('model-name'),
        'namespace'       => $namespace,
        'namespacePrefix' => $bundledNamespace ? $namespaceParts[0] : '',
        'prefix'          => ucfirst($prefix),
    ];
    $sf4              = boolval($input->getArgument('sf4'));
    $dir              = trim(strtr($namespace, '\\', '/'));
    $generator->generateFile(
        sprintf(
            '%sconfig/%s-generator.yml',
            $sf4 ? '' : sprintf('src/%s/Resources/', $dir),
            $prefix
        ),
        __DIR__ . '/../Resources/skeleton/config/yml_generator.tpl.php',
        $parameters
    );

    $dirPrefix = $prefix ? sprintf('%s/', ucfirst($prefix)) : '';
    foreach (self::ACTIONS as $action => $actionProperties) {
      $parameters['action'] = $action;
      $generator->generateFile(
          sprintf(
              'src/%sController/%s%sController.php',
              $sf4 ? '' : sprintf('%s/', $dir),
              $dirPrefix,
              $action
          ),
          __DIR__ . '/../Resources/skeleton/controller/controller_generator.tpl.php',
          $parameters
      );

      foreach ($actionProperties['views'] as $view) {
        $parameters['view'] = $view;
        $generator->generateFile(
            sprintf(
                '%s/%s%s/%s.html.twig',
                $sf4 ? 'templates' : sprintf('src/%s/Resources/views', $dir),
                $dirPrefix,
                $action,
                $view
            ),
            __DIR__ . '/../Resources/skeleton/template/twig_generator.tpl.php',
            $parameters
        );
      }
    }

    foreach (self::FORMS as $form) {
      $parameters['form'] = $form;
      $generator->generateFile(
          sprintf(
              'src/%sForm/Type/%s%sType.php',
              $sf4 ? '' : sprintf('%s/', $dir),
              $dirPrefix,
              $form
          ),
          __DIR__ . '/../Resources/skeleton/form/type_generator.tpl.php',
          $parameters
      );
    }

    $generator->generateFile(
        sprintf(
            'src/%sForm/Type/%sOptions.php',
            $sf4 ? '' : sprintf('%s/', $dir),
            $dirPrefix,
        ),
        __DIR__ . '/../Resources/skeleton/form/options_generator.tpl.php',
        $parameters
    );

    $generator->writeChanges();

    $io->section('Add the following to your routing file to access the admin');
    $io->writeln(sprintf("        resource: \"@%s/Controller/%s/\"\n        type:     admingenerator\n        prefix:   /\n", $bundleName, ucfirst($prefix)));

  }

  /**
   * Validates that the given namespace (e.g. Acme\Foo) is a valid format.
   */
  public static function validateNamespace(string $namespace): string
  {
    $namespace = strtr($namespace, '/', '\\');
    if (!preg_match('/^(?:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*\\\?)+$/', $namespace)) {
      throw new \InvalidArgumentException('The namespace contains invalid characters.');
    }

    // validate reserved keywords
    foreach (explode('\\', $namespace) as $word) {
      if (in_array(strtolower($word), self::RESERVED_KEYWORDS)) {
        throw new \InvalidArgumentException(sprintf('The namespace cannot contain PHP reserved words ("%s").', $word));
      }
    }

    return $namespace;
  }

  const RESERVED_KEYWORDS = [
      'abstract',
      'and',
      'array',
      'as',
      'break',
      'callable',
      'case',
      'catch',
      'class',
      'clone',
      'const',
      'continue',
      'declare',
      'default',
      'do',
      'else',
      'elseif',
      'enddeclare',
      'endfor',
      'endforeach',
      'endif',
      'endswitch',
      'endwhile',
      'extends',
      'final',
      'finally',
      'for',
      'foreach',
      'function',
      'global',
      'goto',
      'if',
      'implements',
      'interface',
      'instanceof',
      'insteadof',
      'namespace',
      'new',
      'or',
      'private',
      'protected',
      'public',
      'static',
      'switch',
      'throw',
      'trait',
      'try',
      'use',
      'var',
      'while',
      'xor',
      'yield',
      '__CLASS__',
      '__DIR__',
      '__FILE__',
      '__LINE__',
      '__FUNCTION__',
      '__METHOD__',
      '__NAMESPACE__',
      '__TRAIT__',
      '__halt_compiler',
      'die',
      'echo',
      'empty',
      'exit',
      'eval',
      'include',
      'include_once',
      'isset',
      'list',
      'require',
      'require_once',
      'return',
      'print',
      'unset',
  ];
}
