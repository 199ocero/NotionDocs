<?php

namespace App\Repositories\Notion\Api;

use Notion\Notion;
use Notion\Pages\Page;
use Notion\Blocks\Code;
use App\Models\Settings;
use Notion\Common\Color;
use Notion\Blocks\Heading2;
use Notion\Common\RichText;
use Notion\Pages\PageParent;
use App\Models\NotionDatabase;
use Notion\Blocks\CodeLanguage;
use Notion\Pages\Properties\Title;
use Notion\Blocks\BulletedListItem;
use Notion\Pages\Properties\Select;
use Notion\Pages\Properties\RichTextProperty;
use App\Repositories\Notion\Token\TokenRepository;

class NotionApiRepository
{
    public function storeApiPage($data)
    {
        $token = new TokenRepository;
        $notion = Notion::create($token->token());

        $database = NotionDatabase::find($data['notion_database_id']);
        $databaseId = $database->database_id;
        $parent = PageParent::database($databaseId);

        $title = Title::fromString($data['title']);
        $description = RichTextProperty::fromString($data['description']);
        $method = Select::fromName($data['method']);

        $page = Page::create($parent)
            ->addProperty("Title", $title)
            ->addProperty("Method", $method)
            ->addProperty("Description", $description);

        $params = [];
        
        if($data['params']){
            $params[] = Heading2::fromString("Parameters");
            foreach ($data['params'] as $parameter) {
                $params[] = BulletedListItem::create()
                    ->addText(RichText::fromString($parameter['key'])->bold()->code()->color(Color::Red))
                    ->addText(RichText::fromString(" - " . $parameter['data_type']));
            }
        }

        $body = [];

        if($data['body']){
            $body[] = Heading2::fromString("Request Body");
            $codeLine = [];

            $decodedData = json_decode($data['body'], true);

            $codeLine[] = RichText::fromString("{\n");
            $codeLine = $this->handleJsonData($decodedData, $codeLine, 1);
            $codeLine[] = RichText::fromString("}");

            $body[] = Code::create()->changeText(...$codeLine)->changeLanguage(CodeLanguage::Json);
        }
        
        $settings = Settings::first();
        
        $content = [
            Heading2::fromString("Endpoint"),
            Code::fromString(generateUrl($settings->base_url, $settings->version, $data['endpoint']), CodeLanguage::Bash),
            ...$params,
            ...$body
        ];

        $page = $notion->pages()->create($page, $content);
        
        return $page;
    }

    public function handleJsonData($data, $codeLine, $indentationLevel) {
        $indentation = str_repeat("\t", $indentationLevel);
    
        foreach ($data as $key => $value) {
            $codeLine[] = RichText::fromString($indentation . "\"$key\"")->color(Color::Red);
            $codeLine[] = RichText::fromString(" : ");
    
            if (is_array($value)) {
                $codeLine[] = RichText::fromString("{\n");
                $codeLine = $this->handleJsonData($value, $codeLine, $indentationLevel + 1);
                $codeLine[] = RichText::fromString($indentation . "}");
            } else {
                $codeLine[] = RichText::fromString("\"$value\"")->color(Color::Green);
            }
    
            $codeLine[] = RichText::fromString(", \n");
        }
    
        return $codeLine;
    }

    public function deleteApiPage($data)
    {
        $token = new TokenRepository;
        $notion = Notion::create($token->token());
        $page = $notion->pages()->find($data['page_id']);
        $page = $notion->pages()->delete($page);

        return $page->archived;
    }
}
