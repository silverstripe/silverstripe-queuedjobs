<?php

namespace Symbiote\QueuedJobs\Tests\Jobs;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use SilverStripe\Dev\SapphireTest;
use Symbiote\QueuedJobs\Jobs\RunBuildTaskJob;
use Symbiote\QueuedJobs\Tests\Jobs\RunBuildTaskJobTest\TestOptionsTask;

class RunBuildTaskJobTest extends SapphireTest
{
    protected $usesDatabase = false;

    protected function setUp(): void
    {
        parent::setUp();
        TestOptionsTask::$receivedOptions = null;
    }

    public static function provideTaskOptions(): array
    {
        return [
            'no query string' => [
                'queryString' => null,
                'expected' => ['value' => null, 'flag' => false, 'list' => []],
            ],
            'value option' => [
                'queryString' => 'value=abc',
                'expected' => ['value' => 'abc', 'flag' => false, 'list' => []],
            ],
            'flag option' => [
                'queryString' => 'flag=1',
                'expected' => ['value' => null, 'flag' => true, 'list' => []],
            ],
            'array option' => [
                'queryString' => 'list[]=one&list[]=two',
                'expected' => ['value' => null, 'flag' => false, 'list' => ['one', 'two']],
            ],
            'unknown parameters are ignored' => [
                'queryString' => 'value=abc&notAnOption=xyz',
                'expected' => ['value' => 'abc', 'flag' => false, 'list' => []],
            ],
        ];
    }

    #[DataProvider('provideTaskOptions')]
    public function testProcessPassesTaskOptions(?string $queryString, array $expected): void
    {
        $job = new RunBuildTaskJob(TestOptionsTask::class, $queryString);
        $job->process();

        $this->assertSame($expected, TestOptionsTask::$receivedOptions);
        $this->assertTrue($job->jobFinished());
    }

    public function testProcessRejectsNonBuildTask(): void
    {
        $job = new RunBuildTaskJob(static::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('is not a build task');
        $job->process();
    }
}
