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
            if(auth()->user()->hasRole('collaborator')){
                $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
                $token = NotionToken::where('user_id', $member->invited_by_id)->first()->token;
            }else{
                $token = NotionToken::where('user_id', auth()->id())->first()->token;
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
