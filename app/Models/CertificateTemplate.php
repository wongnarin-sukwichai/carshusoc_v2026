<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class CertificateTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_center_code',
        'name',
        'background_image_path',
        'title',
        'subtitle',
        'signatory1_name',
        'signatory1_title',
        'signatory1_signature_path',
        'signatory2_name',
        'signatory2_title',
        'signatory2_signature_path',
        'signatory3_name',
        'signatory3_title',
        'signatory3_signature_path',
        'signatory4_name',
        'signatory4_title',
        'signatory4_signature_path',
        'border_color',
        'is_default',
    ];

    /**
     * Base64 data URIs for dompdf — it doesn't reliably fetch images over
     * HTTP from local storage, so PDF rendering reads the files directly
     * instead of using the public asset() URL (which the Vue admin preview
     * uses instead, since that runs in a real browser).
     */
    public function backgroundImageDataUri(): ?string
    {
        return $this->fileDataUri($this->background_image_path);
    }

    public function signatureDataUri(int $signatoryNumber): ?string
    {
        $path = $this->{"signatory{$signatoryNumber}_signature_path"};

        return $this->fileDataUri($path);
    }

    private function fileDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/png';
        $contents = base64_encode(Storage::disk('public')->get($path));

        return "data:{$mime};base64,{$contents}";
    }

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }
}
