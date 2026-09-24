<?php

namespace Symbiote\QueuedJobs\Tests;

use AsyncPHP\Doorman\Rule\InMemoryRule;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use Symbiote\QueuedJobs\Services\QueuedJobService;
use Symbiote\QueuedJobs\Tasks\Engines\DoormanRunner;

/**
 * The `DoormanRunner` service name must resolve to the same rule-carrying definition as the FQCN.
 * With no rules, doorman's InMemoryRules::canRunTask() returns true unconditionally, so a runner
 * resolved through a rule-less spec starts child processes with no cap - silently.
 */
class DoormanRunnerServiceTest extends SapphireTest
{
    public function testFullyQualifiedServiceCarriesDefaultRules()
    {
        // Positive control: the FQCN spec in _config/queuedjobs.yml sets DefaultRules
        $runner = Injector::inst()->create(DoormanRunner::class);

        $this->assertInstanceOf(DoormanRunner::class, $runner);
        $this->assertNotEmpty($runner->getDefaultRules());
    }

    public function testShortServiceNameCarriesDefaultRules()
    {
        // The spelling the config comment recommends: '%$DoormanRunner'
        $runner = Injector::inst()->create('DoormanRunner');

        $this->assertInstanceOf(DoormanRunner::class, $runner);
        $this->assertNotEmpty($runner->getDefaultRules(), 'DoormanRunner service carries no DefaultRules');
        $this->assertContainsOnlyInstancesOf(InMemoryRule::class, $runner->getDefaultRules());
    }

    public function testQueueRunnerConfiguredByShortNameCarriesDefaultRules()
    {
        // The real path: a project follows the config comment and sets queueRunner: '%$DoormanRunner'
        Config::modify()->merge(Injector::class, QueuedJobService::class, [
            'properties' => ['queueRunner' => '%$DoormanRunner'],
        ]);
        Injector::inst()->unregisterObjects(QueuedJobService::class);

        $runner = Injector::inst()->create(QueuedJobService::class)->queueRunner;

        $this->assertInstanceOf(DoormanRunner::class, $runner);
        $this->assertNotEmpty(
            $runner->getDefaultRules(),
            'queueRunner resolved through DoormanRunner has no DefaultRules'
        );
    }
}
