<?php

namespace Admingenerator\GeneratorBundle\Command;

use Admingenerator\GeneratorBundle\CacheBuilder\GeneratorCacheBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Completion\CompletionSuggestions;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateBaseClassesCommand extends Command
{
    private const CONFIGURATION = 'configuration';

    protected static $defaultName = 'admin:generate-base-classes';

    protected static $defaultDescription = 'Generates the admin base classes in the project dir.';

    public function __construct(private readonly GeneratorCacheBuilder $generatorCacheBuilder)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(self::CONFIGURATION, InputArgument::OPTIONAL, 'Filename of the generation configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $generatorName = $input->getArgument(self::CONFIGURATION);
        if ($generatorName) {
            $io->title('Generating admin generator base classes for ' . $generatorName);
        } else {
            $io->title('Generating all admin generator base classes');
        }
        $progressBar = $io->createProgressBar();


        $this->generatorCacheBuilder->buildFull($progressBar, $generatorName);

        if (!$generatorName) {
            $io->newLine();
            $io->newLine();
        }
        $io->success('All done!');

        return Command::SUCCESS;
    }

    public function complete(CompletionInput $input, CompletionSuggestions $suggestions): void
    {
        if ($input->mustSuggestArgumentValuesFor(self::CONFIGURATION)) {
            $suggestions->suggestValues(array_map(
                static fn(string $file): string => basename($file),
                $this->generatorCacheBuilder->getFinder()->findAll(),
            ));
        }
    }


}