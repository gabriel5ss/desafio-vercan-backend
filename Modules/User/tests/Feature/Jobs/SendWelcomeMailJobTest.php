<?php

namespace Modules\User\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Bus;
use Modules\User\Jobs\SendWelcomeMailJob;
use Tests\TestCase;

class SendWelcomeMailJobTest extends TestCase
{
    public function test_welcome_mail_job_dispatch(): void
    {
        $name = 'User';
        $email = 'email@test.com';

        SendWelcomeMailJob::dispatch($name, $email);

        Bus::assertDispatched(SendWelcomeMailJob::class);
    }
}
