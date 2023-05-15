<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotionBlock extends Model
{
    use HasFactory;

    protected $table = 'notion_blocks';
    protected $fillable = [
        'page_id',
        'header_block_id',
        'endpoint_block_id',
        'parameters_block_id',
        'body_block_id'
    ];
}
