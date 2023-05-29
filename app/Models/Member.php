<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    // invitation status
    const PENDING = 'pending';
    const ACCEPTED = 'accepted';
    const REJECTED = 'rejected';

    protected $table = 'members';
    protected $fillable = [
        'team_id',
        'invited_by_id',
        'invited_id',
        'status',
        'invitation_sent_at',
        'invitation_response_at',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function invitedBy()
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }

    public function invited()
    {
        return $this->belongsTo(User::class, 'invited_id');
    }
}
