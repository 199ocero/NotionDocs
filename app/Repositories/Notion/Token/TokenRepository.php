<?php

namespace App\Repositories\Notion\Token;

use App\Models\Member;
use App\Models\NotionToken;
use Illuminate\Support\Facades\Crypt;
use Filament\Notifications\Notification;

class TokenRepository
{
    public function token()
    {    
        try {
            $allowedRoutes = [
                'list-notion-databases',
                'create-notion-api',
                'edit-notion-api',
                'list-notion-apis'
            ];
            $url = url()->current();
            $word = 'pages';
            $position = strpos($url, $word);
            if ($position !== false) {
                $pageString = substr($url, $position + strlen($word) + 1);
            }

            if (in_array($pageString, $allowedRoutes)) {
                $token = NotionToken::where('user_id', auth()->id())->first()->token;
            } else {
                $member = Member::where('invited_id', auth()->user()->id)
                            ->where('status', Member::ACCEPTED)
                            ->first();
                $token = NotionToken::where('user_id', $member->invited_by_id)->first()->token;
            }
        
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
