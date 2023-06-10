<?php

namespace App\Filament\Resources\NotionApiResource\Pages;

use App\Models\Member;
use Filament\Pages\Actions;
use App\Models\NotionDatabase;
use App\Services\Notion\Api\ApiService;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\NotionApiResource;
use App\Repositories\Notion\Api\NotionBlocksRepository;

class CreateNotionApi extends CreateRecord
{
    protected static string $resource = NotionApiResource::class;

    protected function handleRecordCreation(array $data): Model
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

        $data['notion_database_id'] = NotionDatabase::where('user_id', auth()->user()->id)->first()->id;

        $api = new ApiService;
        $page = $api->storeApiPage($data);
        if($page === false){
            Notification::make()
            ->danger()
            ->title('Oops! Something went wrong')
            ->body("The database you tried to store this page can't be found. To solve this problem, you can either restore the database in your Notion or you can import new database.")
            ->persistent()
            ->send();
            
            $this->halt();
        }else{
            $data['page_id'] = $page->id;
        
            $blocks = new NotionBlocksRepository;
            $blocks->storeBlocks($page);
            
            return static::getModel()::create($data);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
