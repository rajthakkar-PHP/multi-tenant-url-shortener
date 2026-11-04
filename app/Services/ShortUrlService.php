<?php

namespace App\Services;

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\QueryException;
use RuntimeException;

class ShortUrlService
{
    public function __construct(private ShortCodeGenerator $codes) {}

    public function create(User $creator, string $originalUrl): ShortUrl
    {
        if ($creator->company_id === null) {
            throw new RuntimeException('A short URL creator must belong to a company.');
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $shortCode = $this->codes->generate();

            try {
                return ShortUrl::create([
                    'company_id' => $creator->company_id,
                    'user_id' => $creator->id,
                    'original_url' => $originalUrl,
                    'short_code' => $shortCode,
                ]);
            } catch (QueryException $exception) {
                if (! str_contains($exception->getMessage(), 'short_code')) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException('A unique short URL code could not be generated.');
    }
}
