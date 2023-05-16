<?php

namespace App\Repositories\Notion\Api;

use App\Models\NotionApi;
use App\Models\NotionBlock;
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
                $codeLine[] = (RichText::fromString($parameter['key'])->color(Color::Red));
                $codeLine[] = (RichText::fromString(" - " . $parameter['data_type']));
                $codeLine[] = (RichText::fromString(" (" . $parameter['parameter_type']. ")"));
                $codeLine[] = RichText::fromString("\n");
            }
            array_pop($codeLine);
            $params[] = Code::create()->changeText(...$codeLine)->changeLanguage(CodeLanguage::Bash);
        }else{
            $params[] = Heading2::fromString("Parameters");
            $params[] = Code::create()->changeText(RichText::fromString('//No parameters')->color(Color::Gray))->changeLanguage(CodeLanguage::Bash);
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
        }else{
            $body[] = Heading2::fromString("Request Body");
            $body[] = Code::create()->changeText(RichText::fromString('//No parameters')->color(Color::Gray))->changeLanguage(CodeLanguage::Bash);
        }
        
        $settings = Settings::first();
        
        $headers = [];
    
        if($data['headers']){
            $headers[] = Heading2::fromString("Headers");

            foreach ($data['headers'] as $key => $value) {
                if ($value === true) {
                    foreach ($settings->headers as $header) {
                        if(snakeCase($header['key']) === $key){
                            $codeLineHeader[] = (RichText::fromString($header['key'])->color(Color::Red));
                            $codeLineHeader[] = (RichText::fromString(" - " . $header['value']));
                            $codeLineHeader[] = RichText::fromString("\n");
                        }
                    }
                }   
            }
            array_pop($codeLineHeader);
            $headers[] = Code::create()->changeText(...$codeLineHeader)->changeLanguage(CodeLanguage::Bash);
        }else{
            $headers[] = Heading2::fromString("Headers");
            $headers[] = Code::create()->changeText(RichText::fromString('//No headers')->color(Color::Gray))->changeLanguage(CodeLanguage::Bash);
        }

        $content = [
            ...$headers,
            Heading2::fromString("Endpoint"),
            Code::create()->changeText(RichText::fromString(generateUrl($settings->base_url, $settings->version, $data['endpoint']))->color(Color::Red))->changeLanguage(CodeLanguage::Bash),
            ...$params,
            ...$body
        ];

        $page = $notion->pages()->create($page, $content);

        return $page;
    }

    public function updateApiPage($data)
    {
        $token = new TokenRepository;
        $notion = Notion::create($token->token());

        $pageBlock = NotionBlock::where('page_id', $data['page_id'])->first();
        $blocks = $notion->blocks()->findChildren($pageBlock->page_id);

        $notionPage = NotionApi::where('page_id', $data['page_id'])->first();

        // update page
        if($notionPage->title !== $data['title'] || $notionPage->description !== $data['description'] || $notionPage->method !== $data['method']){
            
            $title = Title::fromString($data['title']);
            $description = RichTextProperty::fromString($data['description']);
            $method = Select::fromName($data['method']);

            $page = $notion->pages()->find($notionPage->page_id);
            $page = $page->addProperty("Title", $title)
                        ->addProperty("Method", $method)
                        ->addProperty("Description", $description);
            
            $notion->pages()->update($page);
        }

        // update page blocks
        foreach ($blocks as $block) {
            $blockId = $block->metadata()->id;
            // Perform your desired operations with $blockId
            
            switch ($blockId) {
                case $pageBlock->header_block_id:
                    // Code to execute when $blockId matches $pageBlock->header_block_id
                    $this->headerBlock($notionPage, $data, $block, $notion);
                    break;
                
                case $pageBlock->endpoint_block_id:
                    // Code to execute when $blockId matches $pageBlock->endpoint_block_id
                    $this->endpointBlock($notionPage, $data, $block, $notion);
                    break;
                
                case $pageBlock->parameters_block_id:
                    // Code to execute when $blockId matches $pageBlock->parameters_block_id
                    $this->parametersBlock($notionPage, $data, $block, $notion);
                    break;
                
                case $pageBlock->body_block_id:
                    // Code to execute when $blockId matches $pageBlock->body_block_id
                    $this->bodyBlock($notionPage, $data, $block, $notion);
                    break;
                
                default:
                    // Code to execute when $blockId doesn't match any of the cases above
                    break;
            }
        }
        
    }

    public function deleteApiPage($data)
    {
        $token = new TokenRepository;
        $notion = Notion::create($token->token());
        $page = $notion->pages()->find($data['page_id']);
        $page = $notion->pages()->delete($page);

        return $page->archived;
    }

    private function handleJsonData($data, $codeLine, $indentationLevel) {
        $indentation = str_repeat("\t", $indentationLevel);
    
        $keys = array_keys($data);
        $lastKey = end($keys);
    
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
    
            if ($key !== $lastKey) {
                $codeLine[] = RichText::fromString(",");
            }
    
            $codeLine[] = RichText::fromString("\n");
        }
    
        return $codeLine;
    }
    
    private function headerBlock($notionPage, $data, $block, $notion)
    {
        $settings = Settings::first();

        if($notionPage->headers !== $data['headers']){
            if($data['headers']){
                foreach ($data['headers'] as $key => $value) {
                    if ($value === true) {
                        foreach ($settings->headers as $header) {
                            if(snakeCase($header['key']) === $key){
                                $codeLineHeader[] = (RichText::fromString($header['key'])->color(Color::Red));
                                $codeLineHeader[] = (RichText::fromString(" - " . $header['value']));
                                $codeLineHeader[] = RichText::fromString("\n");
                            }
                        }
                    }   
                }
                array_pop($codeLineHeader);
                $block = $block->changeText(...$codeLineHeader)->changeLanguage(CodeLanguage::Bash);
            }else{
                $block = $block->changeText(RichText::fromString('//No headers')->color(Color::Gray))->changeLanguage(CodeLanguage::Bash);
            }

            $notion->blocks()->update($block);
        }
    }

    private function endpointBlock($notionPage, $data, $block, $notion)
    {
        if($notionPage->endpoint !== $data['endpoint']){
            $block = $block->changeText(RichText::fromString(generateUrl($settings->base_url, $settings->version, $data['endpoint']))->color(Color::Red))->changeLanguage(CodeLanguage::Bash);
            $notion->blocks()->update($block);
        }
    }

    private function parametersBlock($notionPage, $data, $block, $notion)
    {
        if($notionPage->params !== $data['params']){
            if($data['params']){
                foreach ($data['params'] as $parameter) {
                    $codeLine[] = (RichText::fromString($parameter['key'])->color(Color::Red));
                    $codeLine[] = (RichText::fromString(" - " . $parameter['data_type']));
                    $codeLine[] = (RichText::fromString(" (" . $parameter['parameter_type']. ")"));
                    $codeLine[] = RichText::fromString("\n");
                }
                array_pop($codeLine);
                $block = $block->changeText(...$codeLine)->changeLanguage(CodeLanguage::Bash);
            }else{
                $block = $block->changeText(RichText::fromString('//No parameters')->color(Color::Gray))->changeLanguage(CodeLanguage::Bash);
            }
            $notion->blocks()->update($block);
        }
    }

    private function bodyBlock($notionPage, $data, $block, $notion)
    {
        if($notionPage->body !== $data['body']){
            if($data['body']){
                $codeLine = [];
    
                $decodedData = json_decode($data['body'], true);
    
                $codeLine[] = RichText::fromString("{\n");
                $codeLine = $this->handleJsonData($decodedData, $codeLine, 1);
                $codeLine[] = RichText::fromString("}");
    
                $block = $block->changeText(...$codeLine)->changeLanguage(CodeLanguage::Json);
            }else{
                $block = $block->changeText(RichText::fromString('//No parameters')->color(Color::Gray))->changeLanguage(CodeLanguage::Bash);
            }
            $notion->blocks()->update($block);
        }
    }
}
