<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'post',
        'user_id',
        'image'
      
    ];

        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        public function likes()
        {
            return $this->hasMany(Like::class);
        }
    
}
