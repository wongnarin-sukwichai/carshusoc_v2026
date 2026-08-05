<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\EmailTemplate;
use App\Models\Payment;
use App\Models\TranslationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TranslationCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_translation_pipeline_submit_quote_pay_approve_deliver_download()
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        // 1) user submits a document
        $this->actingAs($user)->post(route('user.translations.store'), [
            'source_lang' => 'ไทย',
            'target_lang' => 'อังกฤษ',
            'document' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $translation = TranslationRequest::firstOrFail();
        $this->assertSame('submitted', $translation->status);
        Storage::disk('local')->assertExists($translation->source_file_path);

        // 2) admin sends a quote
        $this->actingAs($admin, 'admin')->post(route('admin.translation-requests.quote', $translation), [
            'estimated_price' => 450,
            'delivery_days' => 5,
        ])->assertRedirect();

        $translation->refresh();
        $this->assertSame('quote_sent', $translation->status);
        $this->assertEquals(450, $translation->estimated_price);

        // 3) user pays (uploads slip)
        $this->actingAs($user)->post(route('user.payments.store'), [
            'payable_type' => 'translation_request',
            'payable_id' => $translation->id,
            'slip' => UploadedFile::fake()->image('slip.jpg'),
        ])->assertRedirect();

        $payment = Payment::firstOrFail();
        $this->assertSame('pending', $payment->status);
        $this->assertEquals(450, $payment->amount);

        // 4) admin approves payment -> translation moves to "translating"
        $this->actingAs($admin, 'admin')->post(route('admin.payments.approve', $payment))->assertRedirect();

        $this->assertSame('translating', $translation->fresh()->status);

        // 5) admin delivers the translated file
        $this->actingAs($admin, 'admin')->post(route('admin.translation-requests.deliver', $translation), [
            'file' => UploadedFile::fake()->create('resume_en.pdf', 80, 'application/pdf'),
        ])->assertRedirect();

        $translation->refresh();
        $this->assertSame('completed', $translation->status);
        Storage::disk('public')->assertExists($translation->translated_file_path);

        // 6) user downloads it
        $this->actingAs($user)->get(route('user.translations.download', $translation))->assertOk();

        // Another user must not be able to download it.
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser)->get(route('user.translations.download', $translation))->assertForbidden();

        // 7) admin can also view the translated file directly
        $this->actingAs($admin, 'admin')->get(route('admin.translation-requests.translated', $translation))->assertOk();
    }

    public function test_second_slip_upload_is_blocked_while_first_is_still_pending_but_allowed_after_rejection()
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $translation = TranslationRequest::create([
            'user_id' => $user->id,
            'file_name' => 'resume.pdf',
            'source_lang' => 'ไทย',
            'target_lang' => 'อังกฤษ',
            'status' => 'quote_sent',
            'estimated_price' => 450,
        ]);

        $this->actingAs($user)->post(route('user.payments.store'), [
            'payable_type' => 'translation_request',
            'payable_id' => $translation->id,
            'slip' => UploadedFile::fake()->image('slip-1.jpg'),
        ])->assertRedirect();

        $this->assertSame(1, Payment::count());

        // The translation page must report this request as awaiting slip review.
        $this->actingAs($user)->get(route('user.translation'))->assertInertia(fn ($page) => $page
            ->where('pendingPaymentIds', [$translation->id]));

        // A second upload while the first is still pending must not create a duplicate.
        $this->actingAs($user)->post(route('user.payments.store'), [
            'payable_type' => 'translation_request',
            'payable_id' => $translation->id,
            'slip' => UploadedFile::fake()->image('slip-2.jpg'),
        ])->assertRedirect();

        $this->assertSame(1, Payment::count());

        // Once the first slip is rejected, the user must be able to submit a new one.
        $firstPayment = Payment::firstOrFail();
        $this->actingAs($admin, 'admin')
            ->post(route('admin.payments.reject', $firstPayment), ['reason' => 'โอนเงินไม่ครบ'])
            ->assertRedirect();

        // The rejection reason must surface on the user's translation page.
        $this->actingAs($user)->get(route('user.translation'))->assertInertia(fn ($page) => $page
            ->where('pendingPaymentIds', [])
            ->where('rejectionReasons', [$translation->id => 'โอนเงินไม่ครบ']));

        // Admin's queue must show both attempts grouped under one case, not two unrelated rows.
        $this->actingAs($admin, 'admin')->get(route('admin.payments'))->assertInertia(fn ($page) => $page
            ->has('paymentCases.data', 1)
            ->where('paymentCases.data.0.case_key', 'translation_request:'.$translation->id)
            ->where('paymentCases.data.0.latest_status', 'rejected')
            ->has('paymentCases.data.0.payments', 1)
            ->where('paymentCases.data.0.payments.0.rejected_reason', 'โอนเงินไม่ครบ'));

        $this->actingAs($user)->post(route('user.payments.store'), [
            'payable_type' => 'translation_request',
            'payable_id' => $translation->id,
            'slip' => UploadedFile::fake()->image('slip-3.jpg'),
        ])->assertRedirect();

        $this->assertSame(2, Payment::count());

        // Once resubmitted, the reason should no longer show (a newer, pending attempt exists).
        $this->actingAs($user)->get(route('user.translation'))->assertInertia(fn ($page) => $page
            ->where('pendingPaymentIds', [$translation->id])
            ->where('rejectionReasons', []));

        // And the admin case now shows both attempts, with the latest one pending again.
        $this->actingAs($admin, 'admin')->get(route('admin.payments'))->assertInertia(fn ($page) => $page
            ->has('paymentCases.data', 1)
            ->where('paymentCases.data.0.latest_status', 'pending')
            ->has('paymentCases.data.0.payments', 2));
    }

    public function test_approving_a_payment_logs_an_email_without_a_morph_map_violation()
    {
        // Payment wasn't registered in AppServiceProvider's enforced morph
        // map, so approve()'s EmailNotifier::send(..., $payment) call — which
        // needs $payment->getMorphClass() to write email_logs.related_type —
        // used to throw ClassMorphViolationException whenever a
        // 'payment_approved' template existed (i.e. after seeding, in real
        // usage, just not under RefreshDatabase's empty template table).
        Storage::fake('local');

        EmailTemplate::create([
            'key' => 'payment_approved',
            'subject' => 'ชำระเงินสำเร็จ',
            'body' => 'สวัสดี {{name}} รายการ {{item_name}} ได้รับการอนุมัติแล้ว',
        ]);

        $user = User::factory()->create();
        $admin = Admin::factory()->create();

        $translation = TranslationRequest::create([
            'user_id' => $user->id,
            'file_name' => 'resume.pdf',
            'source_lang' => 'ไทย',
            'target_lang' => 'อังกฤษ',
            'status' => 'quote_sent',
            'estimated_price' => 450,
        ]);

        $this->actingAs($user)->post(route('user.payments.store'), [
            'payable_type' => 'translation_request',
            'payable_id' => $translation->id,
            'slip' => UploadedFile::fake()->image('slip.jpg'),
        ])->assertRedirect();

        $payment = Payment::firstOrFail();

        $this->actingAs($admin, 'admin')->post(route('admin.payments.approve', $payment))->assertRedirect();

        $this->assertDatabaseHas('email_logs', [
            'to_email' => $user->email,
            'related_type' => 'payment',
            'related_id' => $payment->id,
        ]);
    }

    public function test_translation_history_is_paginated()
    {
        $user = User::factory()->create();
        $perPage = 5;

        for ($i = 1; $i <= $perPage + 3; $i++) {
            TranslationRequest::create([
                'user_id' => $user->id,
                'file_name' => "document-{$i}.pdf",
                'source_lang' => 'ไทย',
                'target_lang' => 'อังกฤษ',
                'status' => 'submitted',
            ]);
        }

        $this->actingAs($user)->get(route('user.translation'))->assertInertia(fn ($page) => $page
            ->has('requests.data', $perPage)
            ->where('requests.current_page', 1)
            ->where('requests.last_page', 2)
            ->where('requests.next_page_url', fn ($url) => str_contains($url, 'page=2')));

        $this->actingAs($user)->get(route('user.translation', ['page' => 2]))->assertInertia(fn ($page) => $page
            ->has('requests.data', 3)
            ->where('requests.current_page', 2)
            ->where('requests.next_page_url', null));
    }

    public function test_admin_viewing_translated_file_before_delivery_returns_404()
    {
        $admin = Admin::factory()->create();
        $translation = TranslationRequest::create([
            'user_id' => User::factory()->create()->id,
            'file_name' => 'resume.pdf',
            'source_lang' => 'ไทย',
            'target_lang' => 'อังกฤษ',
            'status' => 'translating',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.translation-requests.translated', $translation))
            ->assertNotFound();
    }
}
