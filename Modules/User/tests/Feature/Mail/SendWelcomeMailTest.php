<?php

namespace Modules\User\Tests\Feature\Mail;

use Illuminate\Support\Facades\Mail;
use Modules\User\Mail\SendWelcomeMail;
use Tests\TestCase;

class SendWelcomeMailTest extends TestCase
{
    private string $name;

    private SendWelcomeMail $mail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name = 'User';
        $this->mail = new SendWelcomeMail($this->name);
    }

    public function test_welcome_mail_sent(): void
    {
        $email = 'email@test.com';

        Mail::to($email)->send($this->mail);

        Mail::assertSent(SendWelcomeMail::class);

        $this->assertSame('user::Mail.User.welcome_user_mail', $this->mail->content()->view);

        $this->mail->assertSeeInHtml($this->name);
    }
}
