<?php

namespace Modules\User\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Modules\User\Mail\SendWelcomeMail;

class SendWelcomeMailJob implements ShouldQueue
{
    use Queueable;

    private string $name;

    private string $email;

    /**
     * Create a new job instance.
     */
    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new SendWelcomeMail($this->name));
    }
}
