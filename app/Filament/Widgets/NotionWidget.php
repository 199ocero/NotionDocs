<?php

namespace App\Filament\Widgets;

use App\Models\NotionApi;
use App\Models\NotionDatabase;
use Filament\Widgets\StatsOverviewWidget\Card;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class NotionWidget extends BaseWidget
{
    use HasWidgetShield;
    protected function getCards(): array
    {
        $databaseCount = NotionDatabase::where('user_id', auth()->user()->id ?? 0)->first();
        $apiCount = NotionApi::where('notion_database_id', $databaseCount->id ?? 0)->count();
        return [
            Card::make('Notion Database', $databaseCount==null ? 0 : $databaseCount->count())
                ->description('Total count of databases stored within Notion')
                ->icon('heroicon-s-sparkles')
                ->color('primary'),
            Card::make('Notion Api', $apiCount)
                ->description('Total count of apis stored within Notion')
                ->icon('heroicon-s-code')
                ->color('primary'),
        ];
    }

    public static function canView(): bool
    {
        if(auth()->user()->hasRole('super_admin')){
            return false;
        }else{
            return true;
        }
    }
}
