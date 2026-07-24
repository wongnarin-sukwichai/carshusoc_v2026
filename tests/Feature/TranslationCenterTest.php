<?php

namespace Tests\Feature;

use App\Models\Admin;
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
    }
}
