<?php

namespace App\Filament\Resources\CollaboratorTeamResource\Pages;

use App\Filament\Resources\CollaboratorTeamResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCollaboratorTeams extends ListRecords
{
    protected static string $resource = CollaboratorTeamResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
