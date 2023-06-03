<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Member;
use App\Models\NotionApi;
use App\Rules\JsonOnlyRule;
use Filament\Resources\Form;
use Filament\Resources\Table;
use App\Models\NotionDatabase;
use Filament\Resources\Resource;
use App\Rules\EndpointValidationRule;
use App\Services\Notion\Api\ApiService;
use Illuminate\Database\Eloquent\Model;
use Creagia\FilamentCodeField\CodeField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Awcodes\FilamentBadgeableColumn\Components\Badge;
use App\Filament\Resources\CollaboratorTeamResource\Pages;
use Awcodes\FilamentBadgeableColumn\Components\BadgeableColumn;
use App\Filament\Resources\CollaboratorTeamResource\RelationManagers;

class CollaboratorTeamResource extends Resource
{
    protected static ?string $model = NotionApi::class;

    protected static ?string $navigationIcon = 'heroicon-o-code';

    protected static ?string $navigationGroup = 'Notion Collaborator';

    protected static ?string $navigationLabel = 'Collaborator Api';

    protected static ?string $pluralLabel = null;

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'collaborator-api';

    public static function getPluralLabel(): string
    {
        if (self::$pluralLabel === null) {
            self::$pluralLabel = getTeam()->name;
        }
        return self::$pluralLabel;
    }

    protected static function getNavigationBadge(): ?string
    {
        $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
        $database = NotionDatabase::where('user_id', $member->invited_by_id)->first();
        return static::getModel()::where('notion_database_id', $database->id ?? 0)->count();
    }

    public static function form(Form $form): Form
    {
        $headers = getHeaders();
        
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
                            Forms\Components\Fieldset::make('Headers')
                                ->schema([
                                    ...$headerComponents
                                ])
                                ->columns(6),
                            Forms\Components\TextInput::make('endpoint')
                                ->required()
                                ->label('Endpoint')
                                ->placeholder('Enter Endpoint')
                                ->rules([new EndpointValidationRule]),
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
                                            'Optional' => 'Optional'
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
            ->defaultSort('created_at', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->recordTitle('Notion Api Page')
                    ->before(function (Model $record) {  
                        $api = new ApiService;
                        $api->deleteApiPage($record->toArray());
                    })
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCollaboratorTeams::route('/'),
            'create' => Pages\CreateCollaboratorTeam::route('/create'),
            'edit' => Pages\EditCollaboratorTeam::route('/{record}/edit'),
        ];
    }
    
    public static function canViewAny(): bool
    {
        $member = Member::where('invited_id', auth()->user()->id)
                        ->where('status', Member::ACCEPTED)
                        ->first();

        return $member && auth()->user()->hasRole('collaborator');
    }
}
