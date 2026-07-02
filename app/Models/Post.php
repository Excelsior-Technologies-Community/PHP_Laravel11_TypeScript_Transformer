<?php

namespace App\Models;

use App\Enums\PostStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @typescript
 */
class Post extends Model
{
    protected $fillable = ['title', 'body', 'user_id', 'status'];

    protected $casts = [
        'status' => PostStatus::class,
        'published_at' => 'datetime',
        'meta' => 'array',
    ];

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'user_id' => 'required|integer',
            'status' => 'nullable',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}