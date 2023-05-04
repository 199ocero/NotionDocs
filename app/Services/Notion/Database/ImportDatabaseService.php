<?php

namespace App\Services\Notion\Database;

use Notion\Notion;
use App\Services\Notion\Token\TokenService;
use App\Repositories\Notion\Token\TokenRepository;
use Notion\Search\Query;

class ImportDatabaseService
{
    public function importDatabase()
    {
        $token = new TokenService(new TokenRepository);
        $notion = Notion::create($token->getToken());
        $query =  Query::all();
        return $database->databases()->query($notion, $query->filterByDatabases());
    }
}
