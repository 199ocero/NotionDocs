<?php

namespace App\Repositories\Notion\Token;

use App\Models\NotionToken;
use Illuminate\Support\Facades\Crypt;
use Filament\Notifications\Notification;

class TokenRepository
{
    public function token()
    {
        $token = NotionToken::where('user_id', auth()->id())->first()->token;
        try {
            return Crypt::decryptString($token);
        } catch (DecryptException $e) {
            Notification::make()
                ->danger()
                ->title('There was an error!')
                ->body($e->getMessage())
                ->send();
        }
    }
}
