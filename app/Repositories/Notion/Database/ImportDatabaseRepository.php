<?php

namespace App\Repositories\Notion\Database;

use App\Models\NotionDatabase;
use Filament\Notifications\Notification;

class ImportDatabaseRepository
{
    public function importDatabase($result)
    {
        $databaseArray = explode(',', $result['database']);

        $id = trim($databaseArray[0]); // Trim to remove any leading/trailing spaces
        $titlePlainText = trim($databaseArray[1]); // Trim to remove any leading/trailing spaces

        $database = NotionDatabase::where('database_id', $id)->first();
        
        $existingDatabaseIds = [];
        
        if ($database) {
            $database->user_id = auth()->user()->id;
            $database->title = $titlePlainText;
            $database->save();
            $existingDatabaseIds[] = $id;
        } else {
            NotionDatabase::create([
                'user_id' => auth()->user()->id,
                'database_id' => $id,
                'title' => $titlePlainText
            ]);
            $existingDatabaseIds[] = $id;
        }

        $this->deleteMissingDatabase($existingDatabaseIds);

        Notification::make() 
            ->title('Imported successfully!')
            ->success()
            ->icon('heroicon-o-cloud-download')
            ->send();

    }

    private function deleteMissingDatabase($existingDatabaseIds)
    {
        NotionDatabase::where('user_id', auth()->id())->whereNotIn('database_id', $existingDatabaseIds)->delete();
    }
}
