<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'address',
        'is_active', 'subscription_status', 'trial_ends_at', 'subscription_expires_at',
        'currency', 'timezone', 'country',
        'mpesa_consumer_key', 'mpesa_consumer_secret', 'mpesa_shortcode',
        'mpesa_passkey', 'mpesa_environment',
        'owner_id',
    ];

    protected $casts = [
        'is_active'               => 'boolean',
        'trial_ends_at'           => 'datetime',
        'subscription_expires_at' => 'datetime',
        'mpesa_consumer_key'      => 'encrypted',
        'mpesa_consumer_secret'   => 'encrypted',
        'mpesa_passkey'           => 'encrypted',
    ];

    protected $hidden = [
        'mpesa_consumer_key',
        'mpesa_consumer_secret',
        'mpesa_passkey',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function canAccessSystem(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        return $this->isSubscriptionActive();
    }

    public function isSubscriptionActive(): bool
    {
        if (in_array($this->subscription_status, ['suspended', 'expired'], true)) {
            return false;
        }

        if ($this->subscription_status === 'trial') {
            if (!$this->trial_ends_at) {
                return false;
            }
            return Carbon::now()->lessThan($this->trial_ends_at);
        }

        if ($this->subscription_status === 'active') {
            if (!$this->subscription_expires_at) {
                return true;
            }
            return Carbon::now()->lessThan($this->subscription_expires_at);
        }

        return false;
    }

    public function trialDaysRemaining(): ?int
    {
        if ($this->subscription_status !== 'trial' || !$this->trial_ends_at) {
            return null;
        }
        $diff = Carbon::now()->diffInDays($this->trial_ends_at, false);
        return max(0, (int) $diff);
    }
}