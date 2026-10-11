<?php

namespace App\Services;

use App\Mail\UploadAlert;
use App\Models\Admin;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Admin ke har upload (test/marks/...) par:
 *   1) Admin ko confirmation email
 *   2) Us class ke students/parents ko email
 *   3) WhatsApp (CallMeBot) — sirf tab jab .env me WHATSAPP_API_URL bhari ho
 *
 * Koi bhi failure upload nahi rokti (sab try/catch me).
 */
class UploadNotifier
{
    public function notify(string $subject, string $message, int $classNo, string $section): void
    {
        $this->sendAdminMail($subject, $message);
        $this->sendStudentMails($subject, $message, $classNo, $section);
        $this->sendWhatsApp($subject . ' — ' . $message);
    }

    public function sendAdminMail(string $subject, string $message): void
    {
        try {
            $admin = Admin::first();
            $to    = $admin?->email ?: (string) env('ADMIN_EMAIL');

            if ($to) {
                Mail::to($to)->send(new UploadAlert(
                    '✅ ' . $subject,
                    $message,
                    'Admin'
                ));
            }
        } catch (\Throwable $e) {
            Log::warning('Admin mail fail: ' . $e->getMessage());
        }
    }

    public function sendStudentMails(string $subject, string $message, int $classNo, string $section): void
    {
        try {
            $emails = Student::query()
                ->where('class_no', $classNo)
                ->where('section', $section)
                ->where('status', 'Active')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->pluck('email')
                ->unique()
                ->values();

            foreach ($emails as $email) {
                try {
                    Mail::to($email)->send(new UploadAlert($subject, $message, 'Student'));
                } catch (\Throwable $e) {
                    Log::warning("Student mail fail ({$email}): " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Student mails fail: ' . $e->getMessage());
        }
    }

    /**
     * WhatsApp — CallMeBot style.
     * .env: WHATSAPP_API_URL = https://api.callmebot.com/whatsapp.php?phone=92XXXXXXX&text={text}&apikey=XXXX
     * Abhi khaali hai to bilkul skip ho jata hai.
     */
    public function sendWhatsApp(string $message): void
    {
        $template = (string) env('WHATSAPP_API_URL', '');
        if ($template === '') {
            return;
        }

        try {
            $url = str_replace('{text}', rawurlencode($message), $template);
            Http::timeout(10)->get($url);
        } catch (\Throwable $e) {
            Log::warning('WhatsApp alert fail: ' . $e->getMessage());
        }
    }
}
