<?php

namespace App\Repositories\Notion\Database;

use App\Models\NotionDatabase;
use Filament\Notifications\Notification;

class ImportDatabaseRepository
{
    public function importDatabase($results)
    {
        $existingDatabaseIds = [];
        $databaseNames = [];
        foreach ($results->results as $result) {
            $id = $result->id;
            $titlePlainText = $result->title[0]->plainText;
            $createdTime = $result->createdTime->format('Y-m-d H:i:s');

            $database = NotionDatabase::where('database_id', $id)->first();

            if ($database) {
                $database->title = $titlePlainText;
                $database->created_time = $createdTime;
                $database->save();
                $existingDatabaseIds[] = $id;
                $databaseNames[] = $titlePlainText;
            }else{
                NotionDatabase::create([
                    'database_id' => $id,
                    'title' => $titlePlainText,
                    'created_time' => $createdTime
                ]);
                $existingDatabaseIds[] = $id;
                $databaseNames[] = $titlePlainText;
            }
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
        NotionDatabase::whereNotIn('database_id', $existingDatabaseIds)->delete();
    }
}
