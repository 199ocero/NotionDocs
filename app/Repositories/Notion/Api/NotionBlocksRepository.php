<?php

namespace App\Repositories\Notion\Api;

use App\Models\NotionBlock;
use Notion\Notion;
use Notion\Exceptions\ApiException;
use Filament\Notifications\Notification;
use App\Repositories\Notion\Token\TokenRepository;

class NotionBlocksRepository
{
    public function storeBlocks($page)
    {
        $token = new TokenRepository;
        $notion = Notion::create($token->token());

        try {
            $blocks = $notion->blocks()->findChildren($page->id);
            $createRecord = false;
            $blocksData = [
                'page_id' => $page->id,
                'header_block_id' => null,
                'endpoint_block_id' => null,
                'parameters_block_id' => null,
                'body_block_id' => null,
            ];

            foreach ($blocks as $block) {
                $text = $block->text[0]->plainText;

                if ($createRecord) {
                    if ($blocksData['header_block_id'] === null) {
                        $blocksData['header_block_id'] = $block->metadata()->id;
                    } elseif ($blocksData['endpoint_block_id'] === null) {
                        $blocksData['endpoint_block_id'] = $block->metadata()->id;
                    } elseif ($blocksData['parameters_block_id'] === null) {
                        $blocksData['parameters_block_id'] = $block->metadata()->id;
                    } elseif ($blocksData['body_block_id'] === null) {
                        $blocksData['body_block_id'] = $block->metadata()->id;
                    }
                    
                    // Set $createRecord to false to avoid creating records for subsequent blocks
                    $createRecord = false;
                }

                if (in_array($text, ['Headers', 'Endpoint', 'Parameters', 'Request Body'])) {
                    // Set $createRecord to true to indicate that the next block should be created
                    $createRecord = true;
                }
            }

            NotionBlock::create($blocksData);
            
        } catch (ApiException $exception) {
            Notification::make()
                ->danger()
                ->title('There was an error!')
                ->body($exception->getMessage())
                ->send();
            
            throw $exception;
        }
    }
}
