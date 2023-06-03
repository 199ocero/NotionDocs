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
                ->label(function (): string {
                    $settings = getHeaders();
                    if($settings){
                        return 'Create Api'; 
                    }
                    return "Settings Await: Owner Hasn't Configured Them Yet";
                })
                ->icon('heroicon-o-link')
                ->color('primary')
                ->disabled(function (): bool {
                    $settings = getHeaders();
                    if($settings){
                        return false;
                    }
                    return true;
                })
        ];
    }

    protected function getTableQuery(): Builder
    {
        $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
        $database = NotionDatabase::where('user_id', $member->invited_by_id)->first();
        return parent::getTableQuery()->where('notion_database_id', $database->id ?? 0);
    }
}
