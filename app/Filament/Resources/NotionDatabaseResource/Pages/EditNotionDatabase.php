<?php

namespace App\Filament\Resources\NotionDatabaseResource\Pages;

use App\Filament\Resources\NotionDatabaseResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotionDatabase extends EditRecord
{
    protected static string $resource = NotionDatabaseResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }
}
