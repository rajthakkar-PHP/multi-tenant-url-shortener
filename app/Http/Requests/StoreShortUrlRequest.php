<?php

namespace App\Http\Requests;

use App\Models\ShortUrl;
use Illuminate\Foundation\Http\FormRequest;

class StoreShortUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', ShortUrl::class) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'original_url' => trim((string) $this->input('original_url', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'original_url' => [
                'required',
                'string',
                'max:2048',
                'url:http,https',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $scheme = is_string($value) ? parse_url($value, PHP_URL_SCHEME) : null;

                    if (! is_string($scheme) || ! in_array(strtolower($scheme), ['http', 'https'], true)) {
                        $fail('The URL must use HTTP or HTTPS.');
                    }
                },
            ],
        ];
    }
}
