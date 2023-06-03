<?php

namespace App\Filament\Resources\NotionApiResource\Pages;

use App\Models\Team;
use App\Models\Member;
use App\Models\Settings;
use Filament\Pages\Actions;
use App\Models\NotionDatabase;
use App\Services\Notion\Api\ApiService;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\NotionApiResource;

class EditNotionApi extends EditRecord
{
    protected static string $resource = NotionApiResource::class;

    protected function getActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $team = Team::where('user_id', auth()->user()->id)->first();
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

        $team = Team::where('user_id', auth()->user()->id)->first();
        $headers = Settings::where('team_id', $team->id ?? 0)->first();
        
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

        $member = Member::where('invited_id', auth()->user()->id)
                        ->where('status', Member::ACCEPTED)
                        ->first();

        if($member && auth()->user()->hasRole('collaborator')){
            $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
            $data['notion_database_id'] = NotionDatabase::where('user_id', $member->invited_by_id)->first()->id;
        }else{
            $data['notion_database_id'] = NotionDatabase::where('user_id', auth()->user()->id)->first()->id;
        }
        
        $api = new ApiService;
        $api->updateApiPage($data);
    
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
