<?php

namespace App\Filament\Resources\NotionDatabaseResource\Pages;

use App\Filament\Resources\NotionDatabaseResource;
use App\Models\NotionToken;
use App\Services\Notion\Database\ImportDatabaseService;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotionDatabases extends ListRecords
{
    protected static string $resource = NotionDatabaseResource::class;

    protected function getActions(): array
    {
        $notionToken = NotionToken::where('user_id', auth()->id())->first();

        return $notionToken ? [
            Actions\Action::make('import')
                ->label('Import Notion Database')
                ->icon('heroicon-o-cloud-download')
                ->color('primary')
                ->action('importDatabase')
                ->requiresConfirmation()
                ->modalHeading('Import Notion Database')
                ->modalSubheading('This will import all databases from Notion, automatically updating any that require modification, and removing any records that do not exist in our current database.')
                ->modalButton('Yes, import database')
                ->modalWidth('2xl')
        ] : [
            Actions\Action::make('import')
                ->label('Connect Notion')
                ->icon('heroicon-o-exclamation')
                ->color('success')
                ->action(function (){
                    return redirect()->route('login.notion');
                })
                ->requiresConfirmation()
        ];

    }

    public function importDatabase(): void
    {
        $database = new ImportDatabaseService;
        $database->importDatabase();
    }
}
