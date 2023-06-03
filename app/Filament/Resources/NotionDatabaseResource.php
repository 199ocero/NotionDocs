<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotionDatabaseResource\Pages;
use App\Filament\Resources\NotionDatabaseResource\RelationManagers;
use App\Models\NotionDatabase;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class NotionDatabaseResource extends Resource
{
    protected static ?string $model = NotionDatabase::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Notion';

    protected static ?int $navigationSort = 1;

    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('user_id', auth()->id() ?? 0)->count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Database Tile')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Imported Time')
                    ->date('F j, Y \a\t g:i A', 'Asia/Singapore')
                    ->sortable()
                    ->searchable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListNotionDatabases::route('/'),
            // 'create' => Pages\CreateNotionDatabase::route('/create'),
            // 'edit' => Pages\EditNotionDatabase::route('/{record}/edit'),
        ];
    }    
}
