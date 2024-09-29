<?php

namespace App\Service;

class TokenManager
{
    public function __construct(private string $filePath) {}

    public function saveToken(string $username, string $token)
    {
        $tokens = $this->getAllTokens();
        $tokens[$username] = $token;
        file_put_contents($this->filePath, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    public function getToken(string $username): ?string
    {
        $tokens = $this->getAllTokens();
        return $tokens[$username] ?? null;
    }

    public function removeToken(string $username): void
    {
        $tokens = $this->getAllTokens();
        unset($tokens[$username]);
        file_put_contents($this->filePath, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    public function getAllTokens(): array
    {
        if (!file_exists($this->filePath)) {
            return [];
        }

        $content = file_get_contents($this->filePath);
        return json_decode($content, true) ?: [];
    }
}
