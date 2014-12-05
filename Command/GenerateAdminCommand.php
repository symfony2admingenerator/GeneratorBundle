<?php

namespace Admingenerator\GeneratorBundle\Command;

use Admingenerator\GeneratorBundle\Routing\Manipulator\RoutingManipulator;

use Admingenerator\GeneratorBundle\Generator\BundleGenerator;

use Sensio\Bundle\GeneratorBundle\Command\GenerateBundleCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\ChoiceQuestion;

class GenerateAdminCommand extends GenerateBundleCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:generate-bundle')
            ->setDescription('Generate a new bundle with admin generated files')
            ->setDefinition(array(
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the bundle to create'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the bundle'),
                new InputOption('bundle-name', '', InputOption::VALUE_REQUIRED, 'The optional bundle name'),
                new InputOption('structure', '', InputOption::VALUE_NONE, 'Whether to generate the whole directory structure'),
                new InputOption('format', '', InputOption::VALUE_REQUIRED, 'Do nothing but mandatory for extend', 'annotation'),
                new InputOption('generator', '', InputOption::VALUE_REQUIRED, 'The generator service (propel, doctrine, doctrine_odm)', 'doctrine'),
                new InputOption('model-name', '', InputOption::VALUE_REQUIRED, 'Base model name for admin module, without namespace.', 'YourModel'),
                new InputOption('prefix', '', InputOption::VALUE_REQUIRED, 'The generator prefix ([prefix]-generator.yml)'),

            ))
            ->setHelp(<<<EOT
The <info>admin:generate-bundle</info> command helps you generates new admin bundles.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction
(<comment>--namespace</comment> is the only one needed if you follow the
conventions):

<info>php app/console admin:generate-bundle --namespace=Acme/BlogBundle</info>

Note that you can use <comment>/</comment> instead of <comment>\\</comment> for the namespace delimiter to avoid any
problem.

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options:

<info>php app/console admin:generate-bundle --namespace=Acme/BlogBundle --dir=src [--bundle-name=...] --no-interaction</info>

Note that the bundle namespace must end with "Bundle".
EOT
            )
        ;
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Symfony2 admin generator');
        $output->writeln('<comment>Create an admingenerator bundle with generate:bundle</comment>');


        $generator = $input->getOption('generator');
        $question = new ChoiceQuestion(
            'Generator to use (doctrine, doctrine_odm, propel)',
            array('doctrine','doctrine_odm','propel'),
            0
        );
        $question->setErrorMessage('Generator to use have to be doctrine, doctrine_odm or propel.');
        $generator = $questionHelper->ask($input, $output, $question);
        $input->setOption('generator', $generator);

        // Model name
        $modelName = $input->getOption('model-name');
        $question = new Question($questionHelper->getQuestion('Model name', $modelName), $modelName);
        $question->setValidator(function ($answer) {
            if (empty($answer) || preg_match('#[^a-zA-Z0-9]#', $answer)) {
              throw new \RuntimeException('Model name should not contain any special characters nor spaces.');
            }
            return $answer;
        });
        $modelName = $questionHelper->ask($input, $output, $question);
        $input->setOption('model-name', $modelName);

        // prefix
        $prefix = $input->getOption('prefix');
        $question = new Question($questionHelper->getQuestion('Prefix of yaml', $prefix), $prefix);
        $question->setValidator(function ($prefix) { 
            if (!preg_match('/([a-z]+)/i', $prefix)) { 
                throw new \RuntimeException('Prefix have to be a simple word'); 
            } 
            return $prefix; 
        });
        $prefix = $questionHelper->ask($input, $output, $question);
        $input->setOption('prefix', $prefix);

        parent::interact($input, $output);

    }

     /**
     * @see Command
     *
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        if ($input->isInteractive()) {
            $question = new ConfirmationQuestion('Do you confirm generation ?', true);
            if (!$questionHelper->ask($input, $output, $question)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        foreach (array('namespace', 'dir') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $namespace = Validators::validateBundleNamespace($input->getOption('namespace'));
        if (!$bundle = $input->getOption('bundle-name')) {
            $bundle = strtr($namespace, array('\\' => ''));
        }
        $bundle = Validators::validateBundleName($bundle);
        $dir = Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace);
        $format = Validators::validateFormat($input->getOption('format'));
        $structure = $input->getOption('structure');

        $questionHelper->writeSection($output, 'Bundle generation');

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $generatorName = $input->getOption('generator');
        $modelName = $input->getOption('model-name');

        $generator = $this->createGenerator();
        $generator->setGenerator($generatorName);
        $generator->setPrefix($input->getOption('prefix'));

        $generator->generate($namespace, $bundle, $dir, $format, $structure, $generatorName, $modelName);

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);

        // check that the namespace is already autoloaded
        $runner($this->checkAutoloader($output, $namespace, $bundle, $dir));

        // register the bundle in the Kernel class
        $runner($this->updateKernel($questionHelper, $input, $output, $this->getContainer()->get('kernel'), $namespace, $bundle));

        // routing
        $runner($this->updateRouting($questionHelper, $input, $output, $bundle, $format));

        $questionHelper->writeGeneratorSummary($output, $errors);
    }

    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/bundle');
    }

    protected function updateRouting(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output, $bundle, $format)                                                         
    {
        $auto = true;
        if ($input->isInteractive()) { 
            $question = new ConfirmationQuestion('Confirm automatic update of the Routing ?', true);
            $auto = $questionHelper->ask($input, $output, $question);
        }

        $output->write('Importing the bundle routing resource: ');
        $routing = new RoutingManipulator($this->getContainer()->getParameter('kernel.root_dir').'/config/routing.yml');
        $routing->setYamlPrefix($input->getOption('prefix'));

        try {
            $ret = $auto ? $routing->addResource($bundle, 'admingenerator') : false;
            if (!$ret) {
                $help = sprintf("        <comment>resource: \"@%s/Resources/Controller/%s/\"</comment>\n        <comment>type:     admingenerator</comment>", $bundle, ucfirst($input->getOption('prefix')));
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
}
