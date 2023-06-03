<?php

namespace App\Filament\Resources\NotionDatabaseResource\Pages;

use Filament\Forms;
use App\Models\NotionToken;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\NotionDatabaseResource;
use App\Services\Notion\Database\ImportDatabaseService;

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
                ->action(function (array $data){
                    $database = new ImportDatabaseService;
                    $database->importDatabase($data);
                })
                ->form([
                    Forms\Components\Select::make('database')
                        ->label('Database')
                        ->options(function () {
                            $database = new ImportDatabaseService;
                            $results = $database->getDatabase();

                            $resultData = collect();

                            foreach ($results->results as $result) {
                                $id = $result->id;
                                $titlePlainText = $result->title[0]->plainText;
                                
                                $resultData->put($id.','.$titlePlainText, $titlePlainText);
                            }
                            
                            return $resultData->toArray();
                        })
                        ->searchable()
                        ->required(),
                ])
                ->requiresConfirmation()
                ->modalHeading('Import Notion Database')
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

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('user_id', auth()->id() ?? 0);
    }
}
