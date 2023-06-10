<?php

namespace App\Repositories\Notion\Database;

use App\Models\NotionDatabase;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action; 
class ImportDatabaseRepository
{
    public function importDatabase($result)
    {
        $database = $result['database'];
        $parts = str_getcsv($database, ',', '');

        $id = $parts[0];
        $titlePlainText = $parts[1];
        $propertiesData = $parts[2];
        $optionsData = $parts[3];

        $propertiesArray = explode("|", trim($propertiesData, "[]"));
        $optionsArray = explode("|", trim($optionsData, "[]"));

        $properties = [
            'Title',
            'Method',
            'Description',
            'Created Time'
        ];

        $options = [
            "GET-green",
            "POST-orange",
            "PUT-purple",
            "PATCH-blue",
            "DELETE-red"
        ];

        if (count(array_intersect($properties, $propertiesArray)) === count($properties)){
            if (count(array_intersect($options, $optionsArray)) === count($options)){
                
                $database = NotionDatabase::where('user_id', auth()->user()->id)->first();
                
                if ($database) {
                    $database->user_id = auth()->user()->id;
                    $database->database_id = $id;
                    $database->title = $titlePlainText;
                    $database->save();
                } else {
                    NotionDatabase::create([
                        'user_id' => auth()->user()->id,
                        'database_id' => $id,
                        'title' => $titlePlainText
                    ]);
                }

                Notification::make() 
                    ->title('Imported successfully!')
                    ->success()
                    ->icon('heroicon-o-cloud-download')
                    ->send();
            }else{
                Notification::make() 
                    ->title('Oops! Something went wrong!')
                    ->body("The database you've selected with the ***Method*** property doesn't have the right options. Please make sure that the options have these names and colors:<br><br>GET - green<br>POST - orange<br>PUT - purple<br>PATCH - blue<br>DELETE - red")
                    ->danger()
                    ->persistent()
                    ->icon('heroicon-o-x-circle')
                    ->send();
            }
        }else{
            Notification::make() 
                ->title('Oops! Something went wrong!')
                ->body("The database you've selected doesn't have the right properties. Please make a copy of the notion database and place it within the page that the system can currently access.")
                ->danger()
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url(env('NOTION_DATABASE_TEMPLATE_URI'), shouldOpenInNewTab: true)
                        ->close(),
                ])
                ->persistent()
                ->icon('heroicon-o-x-circle')
                ->send();
        }

    }
}
