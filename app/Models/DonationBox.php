<?php

namespace App\Models;

use App\Enums\DonationBoxStatus;
use App\Enums\DonationBoxVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationBox extends Model
{
    /** @use HasFactory<\Database\Factories\DonationBoxFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'purpose',
        'target_amount',
        'current_amount',
        'currency',
        'visibility',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => DonationBoxVisibility::class,
            'status' => DonationBoxStatus::class,
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<DonationBox>  $query
     * @return Builder<DonationBox>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', DonationBoxVisibility::Public);
    }

    /**
     * @param  Builder<DonationBox>  $query
     * @return Builder<DonationBox>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', DonationBoxStatus::Open);
    }

    /**
     * @param  Builder<DonationBox>  $query
     * @return Builder<DonationBox>
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
}
