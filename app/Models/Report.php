<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $fillable = [
        'reporter_user_id',
        'target_type',
        'target_id',
        'reason',
        'status'
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /**
     * Resolve the reported model, e.g. ProviderProfile::find($this->target_id).
     * target_type stores a short key ('provider_profile', 'portfolio_item',
     * 'message') mapped in a config array, rather than the raw class name,
     * so renaming a model later doesn't orphan old reports.
     */

    public function target(): ?Model
    {
        $map = [
            'provider_profile' => ProviderProfile::class,
            'portfolio_item' => PortfolioItem::class,
            'message' => Message::class,
        ];

        $modelClass = $map[$this->target_type] ?? null;

        return $modelClass ? $modelClass::find($this->target_id) : null;
    }
}
