<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Payment;
use App\Models\TranslationRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TranslationRequestSeeder extends Seeder
{
    /**
     * Sample translation requests covering every status admin/TranslationQuotes.vue
     * renders differently (submitted, quote_sent, translating, completed), plus
     * the payment row each of those later stages implies, so the page has real
     * data — and a working "view source"/slip file — out of the box.
     */
    public function run(): void
    {
        $requesters = [
            'translation1@example.com' => 'อรุณี ศรีสุข',
            'translation2@example.com' => 'ธนกร วงศ์ไพศาล',
            'translation3@example.com' => 'ปิยะดา ชัยมงคล',
            'translation4@example.com' => 'ณัฐพล เรืองศรี',
        ];

        $users = [];

        foreach ($requesters as $email => $name) {
            $users[$email] = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'email_verified_at' => now(), 'password' => Hash::make('password')]
            );
        }

        // 1) just submitted — awaiting a quote
        $this->makeRequest($users['translation1@example.com'], [
            'file_name' => 'สัญญาจ้างงาน.pdf',
            'source_lang' => 'ไทย',
            'target_lang' => 'อังกฤษ',
            'status' => 'submitted',
        ]);

        // 2) quoted, user paid, awaiting admin's payment approval
        $quoted = $this->makeRequest($users['translation2@example.com'], [
            'file_name' => 'ใบรับรองผลการเรียน.pdf',
            'source_lang' => 'ไทย',
            'target_lang' => 'อังกฤษ',
            'status' => 'quote_sent',
            'estimated_price' => 600,
            'delivery_date' => now()->addDays(5),
        ]);
        $this->makePayment($quoted, 'pending');

        // 3) payment approved — ready to translate / deliver
        $translating = $this->makeRequest($users['translation3@example.com'], [
            'file_name' => 'Research Abstract.docx',
            'source_lang' => 'อังกฤษ',
            'target_lang' => 'ไทย',
            'status' => 'translating',
            'estimated_price' => 850,
            'delivery_date' => now()->addDays(3),
        ]);
        $this->makePayment($translating, 'approved');

        // 4) fully completed — translated file already delivered
        $completed = $this->makeRequest($users['translation4@example.com'], [
            'file_name' => 'หนังสือรับรองการทำงาน.pdf',
            'source_lang' => 'ไทย',
            'target_lang' => 'อังกฤษ',
            'status' => 'completed',
            'estimated_price' => 500,
            'delivery_date' => now()->subDay(),
        ], deliverTranslatedFile: true);
        $this->makePayment($completed, 'approved');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function makeRequest(User $user, array $data, bool $deliverTranslatedFile = false): TranslationRequest
    {
        $request = TranslationRequest::firstOrCreate(
            ['user_id' => $user->id, 'file_name' => $data['file_name']],
            $data
        );

        // Str::slug() on the (often Thai) original file name would strip almost
        // everything, so name the stored file after the request id instead —
        // the original name only needs to survive in the file_name column.
        if (! $request->source_file_path) {
            $sourcePath = 'translations/source/'.$user->id.'/'.$request->id.'.txt';
            Storage::disk('local')->put($sourcePath, 'Demo source document content for seeding.');
            $request->update(['source_file_path' => $sourcePath]);
        }

        if ($deliverTranslatedFile && ! $request->translated_file_path) {
            $translatedPath = 'translations/delivered/'.$request->id.'-translated.txt';
            Storage::disk('public')->put($translatedPath, 'Demo translated document content for seeding.');
            $request->update(['translated_file_path' => $translatedPath]);
        }

        return $request;
    }

    private function makePayment(TranslationRequest $request, string $status): void
    {
        if ($request->payments()->exists()) {
            return;
        }

        $slipPath = 'slips/'.$request->user_id.'/'.$request->id.'-demo-slip.txt';
        Storage::disk('local')->put($slipPath, 'Demo payment slip content for seeding.');

        Payment::create([
            'user_id' => $request->user_id,
            'payable_type' => $request->getMorphClass(),
            'payable_id' => $request->id,
            'amount' => $request->estimated_price,
            'slip_path' => $slipPath,
            'status' => $status,
            'wants_receipt' => false,
            'approved_by' => $status === 'approved' ? Admin::first()?->id : null,
            'approved_at' => $status === 'approved' ? now() : null,
        ]);
    }
}
