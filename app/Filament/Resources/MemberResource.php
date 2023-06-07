<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Member;
use Filament\Resources\Form;
use Filament\Resources\Table;
use Filament\Resources\Resource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MemberResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MemberResource\RelationManagers;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $activeNavigationIcon = 'heroicon-s-users';

    protected static ?string $navigationGroup = 'Team';

    protected static ?string $navigationLabel = 'My Team';

    protected static ?string $pluralLabel = 'My Team';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'my-team';

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
                Tables\Columns\TextColumn::make('invited.name')
                    ->label('Invited Member')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invited.email')
                    ->label('Invited Email')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->enum([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                    ])
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                    ])
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invitation_sent_at')
                    ->label('Sent Date')
                    ->date('F j, Y \a\t g:i A', 'Asia/Singapore')
                    ->sortable(),
                Tables\Columns\TextColumn::make('invitation_response_at')
                    ->label('Response Date')
                    ->date('F j, Y \a\t g:i A', 'Asia/Singapore')
                    ->sortable()
            ])
            ->defaultSort('invitation_sent_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('reinvite')
                    ->action(function (Member $record): void {
                        $inviteAccept = Member::where('invited_id', $record->invited_id)
                                            ->where('status', Member::ACCEPTED)
                                            ->first();
                        if($inviteAccept) {
                            Notification::make() 
                                ->title("Oops! Something went wrong.")
                                ->body("Sorry, but you can't send another invitation to this user as they have already accepted an invitation from another team.")
                                ->danger()
                                ->send();
                        }else{
                            $record->update([
                                'status' => Member::PENDING,
                                'invitation_sent_at' => now(),
                                'invitation_response_at' => null,
                            ]);

                            Notification::make() 
                                ->title('Invitation sent successfully!')
                                ->success()
                                ->send();
                        } 
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Sent Invitation')
                    ->icon('heroicon-s-user-add')
                    ->color('success')
                    ->hidden(function (Member $record): bool {
                        if($record->status === Member::REJECTED) {
                            return false;
                        }else {
                            return true;
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label(function (Member $record): string {
                        if ($record->status === Member::ACCEPTED) {
                            return 'Remove';
                        }else if ($record->status === Member::REJECTED) {
                            return 'Delete';
                        }
                        return 'Cancel';
                    })
                    ->modalHeading(function (Member $record): string {
                        if ($record->status === Member::ACCEPTED) {
                            return 'Remove Member';
                        }else if ($record->status === Member::REJECTED) {
                            return 'Delete Invitation';
                        }
                        return 'Cancel Invitation';
                    })
                    ->modalButton('Yes, confirm!')
                    ->before(function (Member $record) {
                        if ($record->status === Member::ACCEPTED) {
                            User::find($record->invited_id)->removeRole('collaborator');
                        }
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMembers::route('/'),
        ];
    }    
}
