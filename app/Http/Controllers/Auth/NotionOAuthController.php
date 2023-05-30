<?php

namespace App\Http\Controllers\Auth;

use App\Models\NotionToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Laravel\Socialite\Facades\Socialite;

class NotionOAuthController extends Controller
{
    public function redirectToProvider()
    {
        return Socialite::driver('notion')->redirect();
    }

    public function handleProviderCallback()
    {
        $token = Socialite::driver('notion')->user();

        // Save the access token in the database
        NotionToken::create([
            'user_id' => auth()->user()->id,
            'token' => Crypt::encryptString($token->token ),
        ]);

        // Redirect to the desired page
        return redirect()->route('filament.resources.notion-databases.index');
    }
}