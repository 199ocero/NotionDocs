<?php

namespace App\Filament\Widgets;

use App\Models\NotionApi;
use App\Models\NotionDatabase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class NotionWidget extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Notion Database', NotionDatabase::count())
                ->description('Total count of databases stored within Notion')
                ->icon('heroicon-s-sparkles')
                ->color('primary'),
            Card::make('Notion Api', NotionApi::count())
                ->description('Total count of apis stored within Notion')
                ->icon('heroicon-s-code')
                ->color('primary'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('admin');
    }
}
