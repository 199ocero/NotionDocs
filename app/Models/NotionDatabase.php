<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotionDatabase extends Model
{
    use HasFactory;

    protected $table = 'notion_databases';
    protected $fillable = [
        'user_id',
        'database_id',
        'title',
        'created_time',
    ];
}
