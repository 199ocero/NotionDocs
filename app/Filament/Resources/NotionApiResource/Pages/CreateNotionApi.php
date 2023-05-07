<?php

namespace App\Filament\Resources\NotionApiResource\Pages;

use App\Models\Settings;
use Filament\Pages\Actions;
use App\Services\Notion\Api\ApiService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\NotionApiResource;

class CreateNotionApi extends CreateRecord
{
    protected static string $resource = NotionApiResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $headers = Settings::first();
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
        $page = $api->storeApiPage($data);
        $data['page_id'] = $page->id;

        return static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
