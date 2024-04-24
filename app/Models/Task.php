<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'title', 'description', 'due_date', 'priority', 'completed',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $visible = ['title', 'description', 'due_date', 'priority', 'completed', 'user_id', 'created_at', 'updated_at'];

    /**
     * Relationship: A task belongs to a user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
