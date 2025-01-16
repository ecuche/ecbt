<?php
declare(strict_types=1);
namespace Framework\Helpers;
class Token
{
    protected $token;

    public function __construct(string $token_value = null)
    {
            $this->token = $token_value ?? bin2hex(random_bytes(16));
    }

    public function getValue() : string
    {
        return $this->token;    
    }

    public function getHash() : string
    {
        return hash_hmac('sha256', $this->token, $_ENV['SECRET_KEY']);
    }
}