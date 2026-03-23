<?php

namespace App\Mail;

use App\Models\PasswordResetToken;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;
    public string $userName;
    public string $expiresAt;

    public function __construct(PasswordResetToken $token)
    {
        $this->resetUrl  = url("/reset-password/{$token->token}");
        $this->userName  = $token->user->name;
        $this->expiresAt = $token->expires_at->format('d M Y H:i');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[GoTiket] Permintaan Reset Password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
        );
    }
}
