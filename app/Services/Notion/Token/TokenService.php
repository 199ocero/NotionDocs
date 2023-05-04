<?php

namespace App\Services\Notion\Token;

use App\Repositories\Notion\Token\TokenRepository;

class TokenService
{
    private $token;
    public function __construct(TokenRepository $token)
    {
        $this->token = $token;
    }

    public function getToken()
    {
        return $this->token->token();
    }
}
