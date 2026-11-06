<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode,
        public string $userName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'رمز التحقق من الحساب - نادي الأبطال الرياضي',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div dir='rtl' style='font-family: sans-serif; background: #0f172a; color: #f8fafc; padding: 32px; border-radius: 16px; max-width: 480px; margin: auto; border: 1px solid #1e293b;'>
                    <h2 style='color: #f59e0b; margin-top: 0;'>مرحباً {$this->userName}،</h2>
                    <p style='color: #cbd5e1; font-size: 14px; line-height: 1.6;'>استخدم رمز التحقق التالي لتأكيد الدخول إلى حسابك. هذا الرمز صالح لمدة <strong>5 دقائق</strong> فقط:</p>
                    <div style='background: #020617; border: 1px solid #f59e0b; padding: 16px; text-align: center; font-size: 28px; font-weight: bold; letter-spacing: 8px; color: #f59e0b; border-radius: 12px; margin: 24px 0; font-family: monospace;'>
                        {$this->otpCode}
                    </div>
                    <p style='color: #64748b; font-size: 12px; margin-bottom: 0;'>إذا لم تطلب هذا الرمز، يرجى تجاهل هذه الرسالة.</p>
                </div>
            "
        );
    }
}
