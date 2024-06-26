<?php

namespace App\Filament\Resources\NotionApiResource\Pages;

use App\Models\Member;
use Filament\Pages\Actions;
use App\Models\NotionDatabase;
use App\Services\Notion\Api\ApiService;
use Filament\Notifications\Notification;
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
        $headers = getHeaders();
        
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
        $result = $api->updateApiPage($data);
        if($result === false){
            Notification::make()
                ->danger()
                ->title('Oops! Something went wrong')
                ->body("The page you tried to update can't be found. To solve this problem, you can either restore the page in your Notion or delete this page here and create a new one. There is also a possibility that you remove the ***integration*** from your Notion.")
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
