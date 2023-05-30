<?php

namespace App\Filament\Resources\CollaboratorResource\Pages;

use App\Models\Member;
use Filament\Pages\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ManageRecords;
use App\Filament\Resources\CollaboratorResource;

class ManageCollaborators extends ManageRecords
{
    protected static string $resource = CollaboratorResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->where('invited_id', auth()->id())
            ->whereIn('status', [Member::ACCEPTED, Member::PENDING]);
    }
}
