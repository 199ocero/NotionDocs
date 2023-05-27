<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    // invitation status
    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const REJECTED = 'rejected';

    use HasFactory;

    protected $table = 'teams';
    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];
}
