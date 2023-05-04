<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotionApi extends Model
{
    use HasFactory;

    protected $table = 'notion_apis';
    protected $fillable = [
        'notion_database_id',
        'page_id',
        'title',
        'description',
        'method',
        'endpoint',
        'params',
        'body',
        'headers',
    ];

    protected $casts = [
        'params' => 'array',
        'body' => 'array',
        'headers' => 'array',
    ];

    public function notionDatabase()
    {
        return $this->belongsTo(NotionDatabase::class);
    }
}
