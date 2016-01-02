<?php

namespace Admingenerator\GeneratorBundle\Command;

use Admingenerator\GeneratorBundle\Routing\Manipulator\RoutingManipulator;
use Admingenerator\GeneratorBundle\Generator\BundleGenerator;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Sensio\Bundle\GeneratorBundle\Model\Bundle;
use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\KernelInterface;

class GenerateAdminCommand extends GeneratorCommand
{
    protected function configure()
    {
        $this
            ->setName('admin:generate-admin')
            ->setDescription('Generate new admin pages given a model')
            ->setDefinition(array(
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the bundle to use'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where the bundle is', 'src/'),
                new InputOption('bundle-name', '', InputOption::VALUE_REQUIRED, 'The bundle name'),
                new InputOption('generator', '', InputOption::VALUE_REQUIRED, 'The generator service (propel, doctrine, doctrine_odm)', 'doctrine'),
                new InputOption('model-name', '', InputOption::VALUE_REQUIRED, 'Base model name for admin module, without namespace.', 'YourModel'),
                new InputOption('prefix', '', InputOption::VALUE_REQUIRED, 'The generator prefix ([prefix]-generator.yml)'),

            ))
            ->setHelp(<<<EOT
The <info>admin:generate-admin</info> command helps you generates new admin pages for a given model.
This command creates the bundle and register it if it doesn't exists.

By default, the command interacts with the developer to tweak the generation.
Any passed option will be used as a default value for the interaction.

If you want to disable any user interaction, use <comment>--no-interaction</comment> but don't forget to pass all needed options.

Note that the bundle namespace must end with "Bundle".
EOT
            )
        ;
    }
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Symfony2Admingenerator');

        /*
         * Namespace option
         */
        $askForBundleName = true;
        $namespace = $input->getOption('namespace');
        $output->writeln(array(
            '',
            'Precise the full bundle namespace where you want to generate files (including vendor name if any)',
            ''
        ));

        $question = new Question($questionHelper->getQuestion(
            'Fully qualified bundle name',
            $namespace
        ), $namespace);
        $question->setValidator(function ($inputNamespace) {
            return Validators::validateBundleNamespace($inputNamespace, false);
        });
        $namespace = $questionHelper->ask($input, $output, $question);

        if (strpos($namespace, '\\') === false) {
            // this is a bundle name (FooBundle) not a namespace (Acme\FooBundle)
            // so this is the bundle name (and it is also the namespace)
            $input->setOption('bundle-name', $namespace);
            $askForBundleName = false;
        }
        $input->setOption('namespace', $namespace);

        /*
         * bundle-name option
         */
        if ($askForBundleName) {
            $bundle = $input->getOption('bundle-name');
            // no bundle yet? Get a default from the namespace
            if (!$bundle) {
                $bundle = strtr($namespace, array('\\Bundle\\' => '', '\\' => ''));
            }

            $output->writeln(array(
                '',
                'Please specify the Bundle name.',
                'Based on the namespace, we suggest <comment>'.$bundle.'</comment>.',
                '',
            ));
            $question = new Question($questionHelper->getQuestion(
                'Bundle name',
                $bundle
            ), $bundle);
            $question->setValidator(function($bundleName){
                return Validators::validateBundleName($bundleName);
            });
            $bundle = $questionHelper->ask($input, $output, $question);
            $input->setOption('bundle-name', $bundle);
        }

        /*
         * dir option
         */
        // defaults to src/ in the option
        $dir = $input->getOption('dir');
        $output->writeln(array(
            '',
            'Bundles are usually generated into the <info>src/</info> directory. Unless you\'re',
            'doing something custom, hit enter to keep this default!',
            '',
        ));

        $question = new Question($questionHelper->getQuestion(
            'Target Directory',
            $dir
        ), $dir);
        $dir = $questionHelper->ask($input, $output, $question);
        $input->setOption('dir', $dir);


        /*
         * Generator option
         */
        $generator = $input->getOption('generator');
        $output->writeln(array(
            '',
            'What database manager are you using?',
            ''
        ));

        $question = new Question($questionHelper->getQuestion(
            'Generator (doctrine, doctrine_odm, propel)',
            $generator
        ), $generator);
        $question->setValidator(function($generator){
            if (!in_array($generator, array('doctrine', 'doctrine_odm', 'propel'))) {
                throw new \InvalidArgumentException('Use a valid generator.');
            }

            return $generator;
        });
        $question->setAutocompleterValues(array('doctrine', 'doctrine_odm', 'propel'));
        $generator = $questionHelper->ask($input, $output, $question);
        $input->setOption('generator', $generator);


        /*
         * Model name option
         */
        $modelName = $input->getOption('model-name');
        $output->writeln(array(
            '',
            'What is the model name you want to generate files for?',
            ''
        ));
        $question = new Question($questionHelper->getQuestion(
            'Model name',
            $modelName
        ), $modelName);
        $question->setValidator(function ($modelName) {
            if (empty($modelName) || preg_match('#[^a-zA-Z0-9]#', $modelName)) {
                throw new \InvalidArgumentException('Model name should not contain any special characters nor spaces.');
            }

            return $modelName;
        });
        $modelName = $questionHelper->ask($input, $output, $question);
        $input->setOption('model-name', $modelName);

        /*
         * Prefix option
         */
        $prefix = $input->getOption('prefix');
        $output->writeln(array(
            '',
            'Please precise a prefix to use for YAML generator file',
            ''
        ));
        if (!$prefix) {
            $prefix = preg_replace('/[0-9]/', '', $modelName);
        }
        $question = new Question($questionHelper->getQuestion(
            'Prefix of yaml',
            $prefix
        ), $prefix);
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();

        $bundle = $this->createBundleObject($input);
        $questionHelper->writeSection($output, 'Bundle generation');

        $generator = $this->createGenerator();
        $generator->setGenerator($input->getOption('generator'));
        $generator->setPrefix($input->getOption('prefix'));
        $generator->generate(
            $bundle,
            $input->getOption('model-name')
        );

        $output->writeln('Generating the bundle code: <info>OK</info>');

        $errors = array();
        $runner = $questionHelper->getRunner($output, $errors);

        // check that the namespace is already autoloaded
        $runner($this->checkAutoloader($output, $bundle));

        // register the bundle in the Kernel class
        $runner($this->updateKernel($output, $this->getContainer()->get('kernel'), $bundle));

        // routing
        $runner($this->updateRouting($output, $bundle, $input->getOption('prefix')));

        $questionHelper->writeGeneratorSummary($output, $errors);
    }

    protected function createGenerator()
    {
        return new BundleGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/bundle');
    }

    /**
     * @param OutputInterface $output
     * @param Bundle $bundle
     * @return array
     */
    protected function checkAutoloader(OutputInterface $output, Bundle $bundle)
    {
        $output->write('> Checking that the bundle is autoloaded: ');
        if (!class_exists($bundle->getBundleClassName())) {
            return array(
                '- Edit the <comment>composer.json</comment> file and register the bundle',
                '  namespace in the "autoload" section.',
                '',
            );
        }

        return array();
    }

    protected function updateKernel(OutputInterface $output, KernelInterface $kernel, Bundle $bundle)
    {
        $kernelManipulator = new KernelManipulator($kernel);

        $output->write(sprintf(
            '> Enabling the bundle inside <info>%s</info>: ',
            $this->makePathRelative($kernelManipulator->getFilename())
        ));

        try {
            $ret = $kernelManipulator->addBundle($bundle->getBundleClassName());

            if (!$ret) {
                $reflected = new \ReflectionObject($kernel);

                return array(
                    sprintf('- Edit <comment>%s</comment>', $reflected->getFilename()),
                    '  and add the following bundle in the <comment>AppKernel::registerBundles()</comment> method:',
                    '',
                    sprintf('    <comment>new %s(),</comment>', $bundle->getBundleClassName()),
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already defined in <comment>AppKernel::registerBundles()</comment>.', $bundle->getBundleClassName()),
                '',
            );
        }

        return array();
    }

    /**
     * @param OutputInterface $output
     * @param Bundle $bundle
     * @param $prefix
     * @return array|void
     */
    protected function updateRouting(OutputInterface $output, Bundle $bundle, $prefix)
    {
        $targetRoutingPath = $this->getContainer()->getParameter('kernel.root_dir').'/config/routing.yml';
        $output->write(sprintf(
            '> Importing the bundle\'s routes from the <info>%s</info> file: ',
            $this->makePathRelative($targetRoutingPath)
        ));
        $routing = new RoutingManipulator($targetRoutingPath);
        $routing->setYamlPrefix($prefix);

        try {
            $ret = $routing->addResource($bundle->getName(), 'admingenerator');
            if (!$ret) {
                $help = sprintf("        <comment>resource: \"@%s/Controller/%s/\"</comment>\n        <comment>type:     admingenerator</comment>\n", $bundle->getName(), ucfirst($prefix));
                $help .= "        <comment>prefix:   /</comment>\n";

                return array(
                    '- Import the bundle\'s routing resource in the app main routing file:',
                    '',
                    sprintf('    <comment>%s:</comment>', $bundle->getName()),
                    $help,
                    '',
                );
            }
        } catch (\RuntimeException $e) {
            return array(
                sprintf('Bundle <comment>%s</comment> is already imported.', $bundle->getName()),
                '',
            );
        }

        return array();
    }

    /**
     * Creates the Bundle object based on the user's (non-interactive) input.
     *
     * @param InputInterface $input
     *
     * @return Bundle
     */
    protected function createBundleObject(InputInterface $input)
    {
        foreach (array('namespace', 'dir') as $option) {
            if (null === $input->getOption($option)) {
                throw new \RuntimeException(sprintf('The "%s" option must be provided.', $option));
            }
        }

        $namespace = Validators::validateBundleNamespace($input->getOption('namespace'), false);
        if (!$bundleName = $input->getOption('bundle-name')) {
            $bundleName = strtr($namespace, array('\\' => ''));
        }
        $bundleName = Validators::validateBundleName($bundleName);
        $dir = $input->getOption('dir');

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }
        // add trailing / if necessary
        $dir = '/' === substr($dir, -1, 1) ? $dir : $dir.'/';

        return new Bundle(
            $namespace,
            $bundleName,
            $dir,
            'yml', // unused
            false // unused
        );
    }
}
