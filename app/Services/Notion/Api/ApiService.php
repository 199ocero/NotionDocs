<?php

namespace App\Services\Notion\Api;

use App\Repositories\Notion\Api\NotionApiRepository;

class ApiService
{
    public function storeApiPage($data)
    {
        $api = new NotionApiRepository;
        return $api->storeApiPage($data);
    }

    public function deleteApiPage($data)
    {
        $api = new NotionApiRepository;
        return $api->deleteApiPage($data);
    }
}
