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

class GenerateAdminAdminCommand extends GenerateBundleCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:generate-admin')
            ->setDescription('Generate admin classes into an existant bundle')
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
The <info>admin:generate-admin</info> command helps you generates new admin controllers into an existant bundle.
EOT
            )
        ;
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Symfony2Admingenerator');
        $output->writeln('<comment>Create controllers for a generator module</comment>');

        $generator = $input->getOption('generator');
        $question = new ChoiceQuestion(
            'Generator to use (doctrine, doctrine_odm, propel)',
            array('doctrine','doctrine_odm','propel'),
            0
        );
        $question->setErrorMessage('Generator to use have to be doctrine, doctrine_odm or propel.');
        $generator = $questionHelper->ask($input, $output, $question);
        $input->setOption('generator', $generator);


        $namespace = null;
        try {
            // validate the namespace option (if any) but don't require the vendor namespace
            $namespace = $input->getOption('namespace') ? Validators::validateBundleNamespace($input->getOption('namespace'), false) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
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
                $question = new Question($questionHelper->getQuestion('Bundle namespace', $input->getOption('namespace')), $input->getOption('namespace'));
                $question->setValidator(function ($answer) {
                        return Validators::validateBundleNamespace($answer, false);

                });
                $namespace = $questionHelper->ask($input, $output, $question);

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

                    $question = new ConfirmationQuestion($questionHelper->getQuestion(
                        sprintf('Keep <comment>%s</comment> as the bundle namespace (choose no to try again)?', $namespace),
                        'yes'
                    ), true);
                    $acceptedNamespace = $questionHelper->ask($input, $output, $question);
                }
            }
            $input->setOption('namespace', $namespace);
        }


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


        // bundle name
        $bundle = null;
        try {
            $bundle = $input->getOption('bundle-name') ? Validators::validateBundleName($input->getOption('bundle-name')) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
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
            $question = new Question($questionHelper->getQuestion('Bundle name', $bundle), $bundle);
            $question->setValidator(
                 array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateBundleName')
            );
            $bundle = $questionHelper->ask($input, $output, $question);
            $input->setOption('bundle-name', $bundle);
        }

        // target dir
        $dir = null;
        try {
            $dir = $input->getOption('dir') ? Validators::validateTargetDir($input->getOption('dir'), $bundle, $namespace) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $dir) {
            $dir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $output->writeln(array(
                '',
                'The bundle can be generated anywhere. The suggested default directory uses',
                'the standard conventions.',
                '',
            ));
            $question = new Question($questionHelper->getQuestion('Target directory', $dir), $dir);
            $question->setValidator(function ($dir) use ($bundle, $namespace) {
                    return Validators::validateTargetDir($dir, $bundle, $namespace);
            });
            $dir = $questionHelper->ask($input, $output, $question);
            $input->setOption('dir', $dir);
        }

        // format
        $format = null;
        try {
            $format = $input->getOption('format') ? Validators::validateFormat($input->getOption('format')) : null;
        } catch (\Exception $error) {
            $output->writeln($questionHelper->getHelperSet()->get('formatter')->formatBlock($error->getMessage(), 'error'));
        }

        if (null === $format) {
            $output->writeln(array(
                '',
                'Determine the format to use for the generated configuration.',
                '',
            ));
            $question = new Question($questionHelper->getQuestion('Configuration format (yml, xml, php, or annotation)', $input->getOption('format')), $input->getOption('format'));
            $question->setValidator(
                array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat')
            );
            $format = $questionHelper->ask($input, $output, $question);
            $input->setOption('format', $format);
        }


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
        $format = Validators::validateFormat($input->getOption('format'));
        $dir = $input->getOption('dir').'/';
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
                $help = sprintf("        <comment>resource: \"@%s/Controller/%s/\"</comment>\n        <comment>type:     admingenerator</comment>\n", $bundle, ucfirst($input->getOption('prefix')));
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
