<?php

namespace App\Filament\Resources\MemberResource\Pages;

use Filament\Forms;
use App\Models\Team;
use App\Models\User;
use App\Models\Member;
use Filament\Pages\Actions;
use App\Services\Team\TeamService;
use Illuminate\Support\HtmlString;
use App\Services\Team\MemberService;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
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

        $user = auth()->user();
        $team = $user ? Team::where('user_id', $user->id)->first() : null;
        $count = $team ? Member::where('team_id', $team->id)->count() : 0;
        $remainingInvites = 3 - $count;
        $color = $remainingInvites > 0 ? "#4ADE80" : "#F87171";

        return [
            Actions\Action::make('invite')
                ->action(function (array $data): void {
                    $data['team_id'] = Team::where('user_id', auth()->id())->first()->id;
                    $data['invited_by_id'] = auth()->id();
                    $data['status'] = Team::PENDING;
                    
                    $member = new MemberService;
                    $result = $member->saveMemberInvitation($data);

                    if($result) {
                        Notification::make()
                            ->success()
                            ->title('Invitation Sent!')
                            ->body('Invitation sent successfully!')
                            ->send();
                    }
                })
                ->form([
                    Forms\Components\Select::make('invited_id')
                        ->required()
                        ->multiple()
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) {
                            $excludeIds = Member::all()->pluck('invited_id')->toArray();
                            return User::where('email', 'like', "%{$search}%")
                                ->where('email', '!=', auth()->user()->email)
                                ->whereHas('roles', function ($query) {
                                    $query->where('name', 'admin');
                                })
                                ->whereNotIn('id', $excludeIds)
                                ->limit(50)
                                ->pluck('email', 'id');
                        })
                        ->getOptionLabelsUsing(fn ($values): array => User::find($values)?->pluck('email', 'id')->toArray())
                        ->label('Email')
                        ->placeholder('Search by email')
                        ->hint(new HtmlString("Invites remaining: <strong><span style='color:{$color}'>" . $remainingInvites . "</span></strong>"))
                        ->helperText(new HtmlString("Only registered users will be displayed here, and you can only invite up to three members.<br><span style='color:#F87171'><strong>Note:</strong><i> Please note that it is not possible to invite members who have already been invited to another team.</i></span> "))
                        ->minItems(1)
                        ->maxItems(function () use ($count): int {
                            return 3-$count;
                        })
                ])
                ->label(function () use ($count): string {
                    if(Team::where('user_id', auth()->id())->first()){
                        if($count === 3) {
                            return 'Already invited 3 members!';
                        }
                        return 'Invite Member';
                    }else{
                        return 'Set Team Settings First';
                    }
                })
                ->modalHeading('Invite Team Members')
                ->modalButton('Yes, invite!')
                ->icon('heroicon-o-user-add')
                ->color('primary')
                ->disabled(function () use ($count): bool {
                    if(Team::where('user_id', auth()->id())->first()){
                        if($count === 3) {
                            return true;
                        }
                        return false;
                    }else{
                        return true;
                    }
                }),
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

    protected function getTableQuery(): Builder
    {
        $team = Team::where('user_id', auth()->id())->first();

        return parent::getTableQuery()->where('team_id', $team->id ?? 0);
    }
}
