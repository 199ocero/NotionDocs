<?php

namespace App\Filament\Resources\NotionApiResource\Pages;

use Filament\Forms;
use Filament\Pages\Actions;
use App\Models\NotionDatabase;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Services\Settings\SettingsService;
use App\Filament\Resources\NotionApiResource;
use App\Models\Team;

class ListNotionApis extends ListRecords
{
    protected static string $resource = NotionApiResource::class;

    protected function getActions(): array
    {
        $record = getHeaders();
        
        $base_url = tap(Forms\Components\TextInput::make('base_url')
                        ->label('Base Url')
                        ->required()
                        ->url()
                        ->placeholder('e.g. https://www.notion.so'),
                        function ($input) use ($record) {
                            if ($record) {
                                $input->default($record->base_url);
                            }
                        });

        $version = tap(Forms\Components\TextInput::make('version')
                    ->label('Version')
                    ->required()
                    ->placeholder('e.g v1'),
                    function ($input) use ($record) {
                        if ($record) {
                            $input->default($record->version);
                        }
                    });
        
        $headers = tap(Forms\Components\Repeater::make('headers')
                    ->schema([
                        Forms\Components\TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->placeholder('e.g. Accept'),
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->placeholder('e.g. application/json'),
                        Forms\Components\Select::make('required')
                            ->label('Is Required?')
                            ->required()
                            ->options([
                                'True' => 'Yes',
                                'False' => 'No'
                            ])
                    ])
                    ->columns(3)
                    ->createItemButtonLabel('Add Header')
                    ->required()
                    ->defaultItems(1)
                    ->minItems(1),
                    function ($input) use ($record) {
                        if ($record) {
                            $input->default($record->headers);
                        }
                    });
        return [
            Actions\CreateAction::make()
                ->label(function () use ($record): string {
                    if($record){
                        return 'Create API';
                    }else{
                        return 'Set Settings First';
                    }
                })
                ->icon('heroicon-o-link')
                ->color('primary')
                ->hidden(function () use ($record): bool {
                    if($record){
                        return false;
                    }else{
                        return true;
                    }
                }),
            Actions\Action::make('settings')
                ->action(function (array $data): void {
                    $settings = new SettingsService;
                    $result = $settings->saveSettings($data);

                    if($result) {
                        Notification::make()
                            ->success()
                            ->title('Save Successfully!')
                            ->body('Api settings save successfully!')
                            ->send();
                    }
                })
                ->form([
                    Forms\Components\Card::make()
                        ->schema([
                            $base_url,
                            $version,
                        ])
                        ->columns(2),
                    Forms\Components\Card::make()
                        ->schema([
                            $headers
                        ])
                ])
                ->label(function (): string {
                    if(Team::where('user_id', auth()->user()->id)->first()){
                        return 'Settings';
                    }else{
                        return 'Set Team Settings First';
                    }
                })
                ->icon('heroicon-o-cog')
                ->color('secondary')
                ->disabled(function (): bool {
                    if(Team::where('user_id', auth()->user()->id)->first()){
                        return false;
                    }else{
                        return true;
                    }
                })
        ];
    }

    protected function getTableQuery(): Builder
    {
        $database = NotionDatabase::where('user_id', auth()->user()->id)->first();
        return parent::getTableQuery()->where('notion_database_id', $database->id ?? 0);
    }
}
