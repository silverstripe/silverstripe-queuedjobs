<?php

namespace Symbiote\QueuedJobs\Tests\QueuedJobsTest;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\FieldType\DBDatetime;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJob;

/**
 * Three steps, each of which takes three minutes of (mocked) time. Calls $onStep at the start of
 * each step, so a test can look at the database or run a health check in the middle of the job.
 */
class TestLongStepJob extends AbstractQueuedJob implements QueuedJob, TestOnly
{
    /**
     * @var callable|null function (int $step): void
     */
    public static $onStep = null;

    public function getJobType()
    {
        return QueuedJob::QUEUED;
    }

    public function getTitle()
    {
        return 'A test job with long steps';
    }

    public function setup()
    {
        $this->totalSteps = 3;
    }

    public function process()
    {
        $this->currentStep++;
        if (static::$onStep) {
            (static::$onStep)($this->currentStep);
        }
        DBDatetime::set_mock_now(DBDatetime::now()->getTimestamp() + 180);

        if ($this->currentStep >= 3) {
            $this->isComplete = true;
        }
    }
}
