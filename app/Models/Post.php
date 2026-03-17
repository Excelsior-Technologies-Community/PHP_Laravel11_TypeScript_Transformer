<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @typescript
 */
class Post extends Model
{
    public int $id;
    public string $title;
    public string $body;
    public int $user_id;
}