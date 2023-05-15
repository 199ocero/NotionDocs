<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\NotionApi;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\NotionDatabase;
use Filament\Resources\Resource;
use App\Services\Notion\Api\ApiService;
use Illuminate\Database\Eloquent\Model;
use Creagia\FilamentCodeField\CodeField;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\NotionApiResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use App\Filament\Resources\NotionApiResource\RelationManagers;
use App\Models\Settings;
use App\Rules\EndpointValidationRule;
use App\Rules\JsonOnlyRule;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;

class NotionApiResource extends Resource
{
    protected static ?string $model = NotionApi::class;

    protected static ?string $navigationIcon = 'heroicon-o-code';

    protected static ?string $navigationGroup = 'Notion';

    protected static ?int $navigationSort = 2;

    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        $headers = Settings::first();
        $headerComponents = [];
        if($headers){
            foreach($headers->headers as $header){
                $headerComponents[] = Forms\Components\Toggle::make(snakeCase($header['key']))
                    ->required(filter_var($header['required'], FILTER_VALIDATE_BOOLEAN))
                    ->default(filter_var($header['required'], FILTER_VALIDATE_BOOLEAN));
            }
        }
        return $form
            ->schema([
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Card::make()
                        ->schema([
                            Forms\Components\Hidden::make('page_id'),
                            Forms\Components\Section::make('Headers')
                                ->schema([
                                    ...$headerComponents
                                ])
                                ->columns(6),
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->label('Title')
                                ->placeholder('Enter Title'),
                            Forms\Components\Select::make('method')
                                ->required()
                                ->label('Method')
                                ->placeholder('Select Method')
                                ->options([
                                    'GET' => 'GET',
                                    'POST' => 'POST',
                                    'PUT' => 'PUT',
                                    'PATCH' => 'PATCH',
                                    'DELETE' => 'DELETE'
                                ]),
                            Forms\Components\Textarea::make('description')
                                ->required()
                                ->label('Description')
                                ->placeholder('Enter Description'),
                            Forms\Components\TextInput::make('endpoint')
                                ->required()
                                ->label('Endpoint')
                                ->placeholder('Enter Endpoint')
                                ->rules([new EndpointValidationRule]),
                            Forms\Components\Select::make('notion_database_id')
                                ->required()
                                ->label('Database')
                                ->placeholder('Select Database')
                                ->options(NotionDatabase::all()->pluck('title', 'id')),
                        ]),
                    Forms\Components\Card::make()
                        ->schema([
                            Forms\Components\Repeater::make('params')
                                ->label('Parameters (Optional)')
                                ->createItemButtonLabel('Add Parameter')
                                ->schema([
                                    Forms\Components\TextInput::make('key')
                                        ->required()
                                        ->label('Key')
                                        ->placeholder('Enter Key'),
                                    Forms\Components\Select::make('data_type')
                                        ->required()
                                        ->label('Data Type')
                                        ->placeholder('Select Data Type')
                                        ->options([
                                            'String' => 'String',
                                            'Integer' => 'Integer',
                                            'Boolean' => 'Boolean',
                                            'Float' => 'Float',
                                            'Date' => 'Date',
                                            'Array' => 'Array',
                                            'Object' => 'Object'
                                        ]),
                                    Forms\Components\Select::make('parameter_type')
                                        ->required()
                                        ->label('Parameter Type')
                                        ->placeholder('Select Parameter Type')
                                        ->options([
                                            'Required' => 'Required',
                                            'Hidden' => 'Hidden'
                                        ]),
                                ])
                                ->columns(3)
                                ->defaultItems(0)
                        ]),
                    Forms\Components\Card::make()
                        ->schema([
                            CodeField::make('body')
                                ->label('Request Body (Optional)')
                                ->rules([new JsonOnlyRule()])
                                ->withLineNumbers(),
                        ])
                ])
                ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeableColumn::make('title')
                    ->badges([
                        Badge::make('get')
                            ->label('GET')
                            ->color('#86EFAC')
                            ->visible(fn ($record): bool => $record->method === 'GET'),
                        Badge::make('post')
                            ->label('POST')
                            ->color('#FDBA74')
                            ->visible(fn ($record): bool => $record->method === 'POST'),
                        Badge::make('put')
                            ->label('PUT')
                            ->color('#D8B4FE')
                            ->visible(fn ($record): bool => $record->method === 'PUT'),
                        Badge::make('patch')
                            ->label('PATCH')
                            ->color('#93C5FD')
                            ->visible(fn ($record): bool => $record->method === 'PATCH'),
                        Badge::make('delete')
                            ->label('DELETE')
                            ->color('#FDA4AF')
                            ->visible(fn ($record): bool => $record->method === 'DELETE'),
                    ])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->recordTitle('Notion Api Page')
                    ->before(function (Model $record) {
                        
                        $api = new ApiService;
                        $result = $api->deleteApiPage($record->toArray());
                        if(!$result){
                            Notification::make()
                                ->warning()
                                ->title('There was an error!')
                                ->body('Please try again later.')
                                ->send();
                
                            $record->halt();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotionApis::route('/'),
            'create' => Pages\CreateNotionApi::route('/create'),
            'edit' => Pages\EditNotionApi::route('/{record}/edit'),
        ];
    }
    
    public static function snakeCase($string)
    {
        $lowercase = strtolower($string);
        $snakeCase = str_replace(' ', '_', $lowercase);
        return $snakeCase;
    }
}
