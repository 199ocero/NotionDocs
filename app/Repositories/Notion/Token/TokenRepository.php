<?php

namespace App\Repositories\Notion\Token;

use App\Models\NotionToken;

class TokenRepository
{
    public function token()
    {
        return NotionToken::where('user_id', auth()->id())->first()->token;
    }
}
