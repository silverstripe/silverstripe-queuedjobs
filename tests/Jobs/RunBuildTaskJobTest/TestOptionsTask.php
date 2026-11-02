<?php

namespace Symbiote\QueuedJobs\Tests\Jobs\RunBuildTaskJobTest;

use SilverStripe\Dev\BuildTask;
use SilverStripe\Dev\TestOnly;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Records the option values it receives so tests can assert on them
 */
class TestOptionsTask extends BuildTask implements TestOnly
{
    protected static string $commandName = 'RunBuildTaskJobTestOptionsTask';

    public static ?array $receivedOptions = null;

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        static::$receivedOptions = [
            'value' => $input->getOption('value'),
            'flag' => $input->getOption('flag'),
            'list' => $input->getOption('list'),
        ];

        return Command::SUCCESS;
    }

    public function getOptions(): array
    {
        return [
            new InputOption('value', null, InputOption::VALUE_OPTIONAL, 'An optional value'),
            new InputOption('flag', null, InputOption::VALUE_NONE, 'A boolean flag'),
            new InputOption('list', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'A list'),
        ];
    }
}
