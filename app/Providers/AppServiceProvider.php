<?php

namespace App\Providers;

use App\Models\CourseEnrollment;
use App\Models\ExamRegistration;
use App\Models\TranslationRequest;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Short, stable morph aliases for the payable/certifiable polymorphic
        // columns, so stored type strings don't break if a model gets renamed.
        Relation::enforceMorphMap([
            'course_enrollment' => CourseEnrollment::class,
            'exam_registration' => ExamRegistration::class,
            'translation_request' => TranslationRequest::class,
        ]);

        // Backend doesn't track the user's locale (see CLAUDE.md), so the
        // built-in reset-password email carries both languages side by side
        // instead of picking one.
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $expireMinutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('ตั้งรหัสผ่านใหม่ / Reset Password Notification — CARS-HUSOC')
                ->line('ระบบได้รับคำขอตั้งรหัสผ่านใหม่สำหรับบัญชีของท่าน กดปุ่มด้านล่างเพื่อตั้งรหัสผ่าน')
                ->line("ลิงก์นี้จะหมดอายุใน {$expireMinutes} นาที")
                ->line('หากท่านไม่ได้เป็นผู้ทำรายการนี้ ไม่ต้องดำเนินการใดๆ เพิ่มเติม')
                ->action('ตั้งรหัสผ่านใหม่ / Reset Password', $url)
                ->line('We received a request to set a new password for your account. Click the button above to continue.')
                ->line("This password reset link will expire in {$expireMinutes} minutes.")
                ->line('If you did not request a password reset, no further action is required.');
        });
    }
}
