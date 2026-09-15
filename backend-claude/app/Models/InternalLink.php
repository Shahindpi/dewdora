<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternalLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_post_id', 'target_post_id', 'anchor_text',
        'context', 'is_auto_generated',
    ];

    protected function casts(): array
    {
        return [
            'is_auto_generated' => 'boolean',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'source_post_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'target_post_id');
    }
}
