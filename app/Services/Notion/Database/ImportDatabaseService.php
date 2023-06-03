<?php

namespace App\Services\Notion\Database;

use Notion\Notion;
use Notion\Search\Query;
use App\Services\Notion\Token\TokenService;
use App\Repositories\Notion\Token\TokenRepository;
use App\Repositories\Notion\Database\ImportDatabaseRepository;

class ImportDatabaseService
{
    public function importDatabase($result)
    {
        $import = new ImportDatabaseRepository;
        $import->importDatabase($result);
    }

    /**
     * Returns a Notion database object that can be used to query
     * databases in the Notion API.
     *
     * @throws Some_Exception_Class description of exception
     * @return Database
     */
    public function getDatabase()
    {
        $token = new TokenService(new TokenRepository);
        $notion = Notion::create($token->getToken());
        $query =  Query::all();        
        return $notion->search()->search($query->filterByDatabases());
    }
}
