<?php

namespace App\Filament\Resources\CollaboratorTeamResource\Pages;

use App\Models\Team;
use App\Models\Member;
use App\Models\Settings;
use Filament\Pages\Actions;
use App\Models\NotionDatabase;
use App\Services\Notion\Api\ApiService;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CollaboratorTeamResource;

class EditCollaboratorTeam extends EditRecord
{
    protected static string $resource = CollaboratorTeamResource::class;

    protected static ?string $title = null;

    protected function getTitle(): string
    {
        if (self::$title === null) {
            self::$title = 'Edit '.getTeam()->name.' Api';
        }
        return self::$title;
    }
    protected function getActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
        $team = Team::where('user_id', $member->invited_by_id)->first();
        $headers = Settings::where('team_id', $team->id ?? 0)->first();
        
        $headerKey = [];
        $dataHeaders = $data['headers'];

        if ($headers) {
            foreach ($headers->headers as $header) {
                $headerKey = snakeCase($header['key']);

                if (isset($dataHeaders[$headerKey])) {
                    $data[$headerKey] = $dataHeaders[$headerKey];
                }
            }
            unset($data['headers']);
        }
    
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $headers = getHeaders();
        
        $headerKey = [];
        if ($headers) {
            foreach ($headers->headers as $header) {
                $headerKey[] = snakeCase($header['key']);
            }
        }

        $data['headers'] = [];
        foreach ($headerKey as $key) {
            if (isset($data[$key])) {
                $data['headers'][$key] = $data[$key];
                unset($data[$key]);
            }
        }

        $data['notion_database_id'] = NotionDatabase::where('user_id', $member->invited_by_id)->first()->id;
        
        $api = new ApiService;
        $result = $api->updateApiPage($data);
        if($result === false){
            Notification::make()
            ->danger()
            ->title('Oops! Something went wrong')
            ->body("The page you tried to update can't be found. To solve this problem, you can either restore the page in your Notion or delete this page here and create a new one.")
            ->persistent()
            ->send();
            
            $this->halt();
        }else{
            return $data;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
