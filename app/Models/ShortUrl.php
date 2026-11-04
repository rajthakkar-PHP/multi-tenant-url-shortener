<?php

namespace App\Models;

use Database\Factories\ShortUrlFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrl extends Model
{
    /** @use HasFactory<ShortUrlFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'original_url',
        'short_code',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
