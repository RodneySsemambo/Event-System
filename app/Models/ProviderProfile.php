<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    use HasFactory;
    protected $fillable = [

        'user_id',
        'category_id',
        'business_name',
        'location',
        'whatsapp_number',
        'bio',
        'profile_photo_path',
        'banner_path',
        'price_range_min',
        'price_range_max',
        'verification_status',
        'availability_status',
    ];

    protected function casts()
    {
        return [
            'price_range_min' => 'decimal:2',
            'price_range_max' => 'decimal:2',
        ];
    }

    //----Relationships------------------

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function portfolioItems()
    {
        return $this->hasMany(PortfolioItem::class)->orderBy('sort_order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function availabilities()
    {
        return $this->hasMany(ProviderAvailability::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    // Query Scopes used in the search service 

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function scopeInLocation(Builder $query, string $location): Builder
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopePriceBetween(Builder $query, ?float $min, ?float $max): Builder
    {
        return $query
            ->when($min !== null, fn($q) => $q->where('price_range_max', '>=', $min))
            ->when($max !== null, fn($q) => $q->where('price_range_min', '<=', $max));
    }

    public function scopeKeyword(Builder $query, string $term): Builder
    {
        $kw = '%' . $term . '%';

        return $query->where(function ($q) use ($kw) {
            $q->where('business_name', 'LIKE', $kw)
                ->orWhere('bio', 'LIKE', $kw)
                ->orWhere('location', 'LIKE', $kw)
                ->orWhereHas('category', fn($cq) => $cq->where('name', 'LIKE', $kw));
        });
    }
}
