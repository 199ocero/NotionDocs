<?php

namespace App\Filament\Resources\CollaboratorTeamResource\Pages;

use App\Models\Member;
use App\Models\NotionDatabase;
use App\Services\Notion\Api\ApiService;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\CollaboratorTeamResource;
use App\Repositories\Notion\Api\NotionBlocksRepository;

class CreateCollaboratorTeam extends CreateRecord
{
    protected static string $resource = CollaboratorTeamResource::class;

    protected static ?string $title = null;

    protected function getTitle(): string
    {
        if (self::$title === null) {
            self::$title = 'Create '.getTeam()->name.' Api';
        }
        return self::$title;
    }
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

        $member = Member::where('invited_id', auth()->user()->id)->where('status', Member::ACCEPTED)->first();
        
        $data['notion_database_id'] = NotionDatabase::where('user_id', $member->invited_by_id)->first()->id;

        $api = new ApiService;
        $page = $api->storeApiPage($data);
        if($page === false){
            Notification::make()
                ->danger()
                ->title('Oops! Something went wrong')
                ->body("The database you tried to store this page can't be ***found***. To solve this problem, you can either restore the database in your ***Notion*** or you can import new database. There is also a possibility that you remove the ***integration*** from your Notion.")
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
