<?php

namespace App\Filament\Resources\MemberResource\Pages;

use Filament\Forms;
use App\Models\Team;
use App\Models\User;
use Filament\Pages\Actions;
use App\Services\Team\TeamService;
use Illuminate\Support\HtmlString;
use Filament\Notifications\Notification;
use App\Filament\Resources\MemberResource;
use Filament\Resources\Pages\ManageRecords;

class ManageMembers extends ManageRecords
{
    protected static string $resource = MemberResource::class;

    protected function getActions(): array
    {
        $team = Team::where('user_id', auth()->id())->first();

        $name = tap(Forms\Components\TextInput::make('name')
                        ->required()
                        ->placeholder('e.g. Holazyn'),
                        function ($input) use ($team) {
                            if ($team) {
                                $input->default($team->name);
                            }
                        });
        $description = tap(Forms\Components\Textarea::make('description')
                        ->required()
                        ->placeholder('e.g. Lorem ipsum dolor sit amet.'),
                        function ($input) use ($team) {
                            if ($team) {
                                $input->default($team->description);
                            }
                        });

        return [
            Actions\Action::make('invite')
                ->action(function (array $data): void {
                    $data['team_id'] = Team::where('user_id', auth()->id())->first()->id;
                    $data['invited_by_id'] = auth()->id();
                    $data['status'] = Team::PENDING;
                    dd($data);
                })
                ->form([
                    Forms\Components\Select::make('invited_id')
                        ->required()
                        ->multiple()
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            return User::where('email', 'like', "%{$search}%")
                                ->where('email', '!=', auth()->user()->email)
                                ->whereHas('roles', function ($query) {
                                    $query->where('name', 'admin');
                                })
                                ->limit(50)
                                ->pluck('email', 'id');
                        })
                        ->getOptionLabelsUsing(fn ($values): array => User::find($values)?->pluck('email', 'id')->toArray())
                        ->placeholder('Search by email')
                        ->helperText(new HtmlString("Only registered users will be displayed here, and you can only invite up to three members.<br><span style='color:#F87171'><strong>Note:</strong><i> Please note that it is not possible to invite members who have already been invited to another team.</i></span> "))
                        ->maxItems(3)
                ])
                ->label(function (): string {
                    if(Team::where('user_id', auth()->id())->first()){
                        return 'Invite Member';
                    }else{
                        return 'Set Team Settings First';
                    }
                })
                ->modalHeading('Invite Team Members')
                ->modalButton('Yes, invite!')
                ->icon('heroicon-o-user-add')
                ->color('primary')
                ->disabled(Team::where('user_id', auth()->id())->first() == null ? true : false),
            Actions\Action::make('team')
                ->action(function (array $data): void {
                    $team = new TeamService;
                    $result = $team->saveTeam($data);

                    if($result) {
                        Notification::make()
                            ->success()
                            ->title('Save Successfully!')
                            ->body('Team settings save successfully!')
                            ->send();
                    }
                })
                ->form([
                    $name,
                    $description
                ])
                ->label('Team Settings')
                ->icon('heroicon-o-cog')
                ->color('secondary'),
        ];
    }
}