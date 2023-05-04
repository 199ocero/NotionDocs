<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\NotionToken;
use Illuminate\Http\Request;
use Filament\Notifications\Notification;

class CheckNotionToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $notionToken = NotionToken::where('user_id', auth()->id())->first();

        if ($notionToken) {
            
            Notification::make()
                ->title('Action not allowed.')
                ->icon('heroicon-o-x-circle') 
                ->body('You are not allowed to perform this action.') 
                ->iconColor('danger') 
                ->send();

            return redirect()->route('filament.pages.dashboard');
            
        }
        return $next($request);
    }
}