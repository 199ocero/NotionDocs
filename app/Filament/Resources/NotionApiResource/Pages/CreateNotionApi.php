<?php

namespace App\Filament\Resources\NotionApiResource\Pages;

use App\Models\Team;
use App\Models\Settings;
use Filament\Pages\Actions;
use App\Services\Notion\Api\ApiService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\NotionApiResource;
use App\Repositories\Notion\Api\NotionBlocksRepository;

class CreateNotionApi extends CreateRecord
{
    protected static string $resource = NotionApiResource::class;

    protected function handleRecordCreation(array $data): Model
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
        $page = $api->storeApiPage($data);
        $data['page_id'] = $page->id;
        
        $blocks = new NotionBlocksRepository;
        $blocks->storeBlocks($page);
        
        return static::getModel()::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
