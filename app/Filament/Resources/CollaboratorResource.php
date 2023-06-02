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
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CollaboratorResource\Pages;
use App\Filament\Resources\CollaboratorResource\RelationManagers;

class CollaboratorResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Team';

    protected static ?string $navigationLabel = 'Collaborator';

    protected static ?string $pluralLabel = 'Collaborator';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'collaborator';

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
                Tables\Columns\TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('invitedBy.name')
                    ->label('Invited By')
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
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('accept')
                    ->action(function (Member $record): void {
                        Member::where('invited_id', $record->invited_id)->update([
                            'status' => Member::REJECTED,
                            'invitation_response_at' => now(),
                        ]);

                        User::find($record->invited_id)->assignRole('collaborator');

                        $record->update([
                            'status' => Member::ACCEPTED,
                            'invitation_response_at' => now(),
                        ]);
                        
                        Notification::make() 
                            ->title('Invitation accepted successfully!')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Accept Invitation')
                    ->modalSubheading('Accepting this invitation will automatically reject any other pending invitations. Are you sure you want to proceed?')
                    ->icon('heroicon-s-badge-check')
                    ->color('success')
                    ->hidden(function (Member $record): bool {
                        if($record->status === Member::PENDING) {
                            return false;
                        }else {
                            return true;
                        }
                    }),
                Tables\Actions\Action::make('reject')
                    ->action(function (Member $record): void {
                        $record->update([
                            'status' => Member::REJECTED,
                            'invitation_response_at' => now(),
                        ]);
                        Notification::make() 
                            ->title('Invitation rejected successfully!')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Reject Invitation')
                    ->icon('heroicon-s-x-circle')
                    ->color('danger')
                    ->hidden(function (Member $record): bool {
                        if($record->status === Member::PENDING) {
                            return false;
                        }else {
                            return true;
                        }
                    }),
                Tables\Actions\Action::make('leave')
                    ->action(function (Member $record): void {
                        User::find($record->invited_id)->removeRole('collaborator');
                        $record->delete();
                        Notification::make() 
                            ->title('You have left the team '.$record->team->name.'!')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Leave Team')
                    ->icon('heroicon-s-logout')
                    ->color('danger')
                    ->hidden(function (Member $record): bool {
                        if($record->status === Member::ACCEPTED) {
                            return false;
                        }else {
                            return true;
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
            'index' => Pages\ManageCollaborators::route('/'),
        ];
    }    
}
