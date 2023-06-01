<?php

namespace App\Filament\Resources\NotionApiResource\Pages;

use App\Models\Team;
use App\Models\Settings;
use Filament\Pages\Actions;
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
        
        $api = new ApiService;
        $api->updateApiPage($data);
    
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
