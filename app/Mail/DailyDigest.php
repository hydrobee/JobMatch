<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class DailyDigest extends Mailable
{
    public function __construct(
        public User $user,
        public Collection $jobs,
        public Collection $followUps,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "JobMatch: {$this->jobs->count()} new match(es), {$this->followUps->count()} follow-up(s) due",
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.digest');
    }
}
