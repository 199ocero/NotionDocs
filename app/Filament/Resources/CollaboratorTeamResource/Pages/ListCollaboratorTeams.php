<?php

namespace App\Filament\Resources\CollaboratorTeamResource\Pages;

use App\Models\Member;
use Filament\Pages\Actions;
use App\Models\NotionDatabase;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CollaboratorTeamResource;

class ListCollaboratorTeams extends ListRecords
{
    protected static string $resource = CollaboratorTeamResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Api')
                ->icon('heroicon-o-link')
                ->color('primary')
        ];
    }

    protected function getTableQuery(): Builder
    {
        $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
        $database = NotionDatabase::where('user_id', $member->invited_by_id)->first();
        return parent::getTableQuery()->where('notion_database_id', $database->id ?? 0);
    }
}
