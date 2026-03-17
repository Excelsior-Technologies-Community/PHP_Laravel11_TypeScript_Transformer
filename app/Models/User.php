<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @typescript
 */
class User extends Model
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $password; // nullable
}