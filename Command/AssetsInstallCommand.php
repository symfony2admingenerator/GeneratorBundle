<?php

namespace Admingenerator\GeneratorBundle\Command;

use Admingenerator\GeneratorBundle\Filesystem\RelativePathComputer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class AssetsInstallCommand
 * @package Admingenerator\GeneratorBundle\Command
 * @author Stéphane Escandell
 *
 * Automatically call bower install to properly import bowers components.
 * Push them to the web root directory
 */
class AssetsInstallCommand extends Command
{

    /**
     * @var string
     */
    private $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
        parent::__construct();
    }

  /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('admin:assets-install')
            ->setDescription('Fetch bower declared dependencies and push them into web root directory')
            ->setHelp('The <info>admin:assets-install</info> command fetch bower dependencies (CSS and JS files) to the web root dir.')
            ->setDefinition(array(
                new InputOption('mode', 'm', InputOption::VALUE_OPTIONAL, 'Mode to fetch dependencies', 'install'),
                new InputOption('bower-bin', 'b', InputOption::VALUE_REQUIRED, 'Path to the bower binary', 'bower')
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bowerFileLocation = dirname(dirname(__FILE__));
        $targetDir = $this->computeTargetDirectory($bowerFileLocation);
        $formatter = $this->getHelperSet()->get('formatter');

        $cmd = sprintf(
            '%s %s --config.directory=%s',
            $input->getOption('bower-bin'),
            $input->getOption('mode'),
            $targetDir
        );

        if (OutputInterface::VERBOSITY_VERBOSE <= $output->getVerbosity()) {
            $output->writeln($formatter->formatSection('Bower', sprintf('Running command %s', $cmd)));
        }

        $process = new Process($cmd);
        $process->setTimeout(300);
        $process->setWorkingDirectory($bowerFileLocation);
        $output->writeln($formatter->formatSection('Bower', sprintf('Fetching vendors using the <info>%s</info> mode.', $input->getOption('mode'))));
        $process->run(function ($type, $buffer) use ($output, $formatter) {
            if (Process::ERR == $type) {
                $output->write($formatter->formatBlock($buffer, 'error'));
            } else {
                $output->write($formatter->formatSection('Bower', $buffer, 'info' ));
            }
        });
        
        return $process->getExitCode();
    }

    /**
     * Compute relative path from $bowerFileDirectory to the web directory
     *
     * @param string $bowerFileDirectory
     * @return string
     */
    private function computeTargetDirectory($bowerFileDirectory)
    {
        $parentWebDir = dirname($this->rootDir);
        $relativePathComputer = new RelativePathComputer($bowerFileDirectory);

        return $relativePathComputer->computeToParent($parentWebDir) . 'web' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'components';
    }
}
