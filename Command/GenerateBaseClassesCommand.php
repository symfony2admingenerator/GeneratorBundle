<?php

namespace Admingenerator\GeneratorBundle\Command;

use Admingenerator\GeneratorBundle\CacheBuilder\GeneratorCacheBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateBaseClassesCommand extends Command
{
    protected static $defaultName = 'admin:generate-base-classes';

    protected static $defaultDescription = 'Generates the admin base classes in the project dir.';

    public function __construct(private readonly GeneratorCacheBuilder $generatorCacheBuilder)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generating admin generator base classes');
        $progressBar = $io->createProgressBar();

        $this->generatorCacheBuilder->buildFull($progressBar);

        $io->newLine();
        $io->newLine();
        $io->success('All done!');

        return Command::SUCCESS;
    }
}