<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MfaCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
    ) {}

    public function build(): self
    {
        return $this->subject('Your MFA Verification Code')
            ->markdown('mail.mfa-code', ['code' => $this->code]);
    }
}
