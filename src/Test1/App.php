<?php

namespace RDAlfaGroup\Test1;

class App
{
    private static self $_instance;
    private TokenStoreInterface $_store;

    public static function instance(): self
    {
        return self::$_instance ?? (self::$_instance = new self());
    }

    public function withTokenStore(TokenStoreInterface $store): self
    {
        $this->_store = $store;
        return $this;
    }

    public function encrypt(string $url): string
    {
        [$path, $query] = $this->parseUrl($url);
        $hashes = $this->_store->setTokens(array_keys($query));

        return $this->buildUrl($path, $query, $hashes);
    }

    public function decrypt(string $url): string
    {
        [$path, $query] = $this->parseUrl($url);
        $tokens = $this->_store->getTokens(array_keys($query));

        return $this->buildUrl($path, $query, $tokens);
    }

    private function parseUrl(string $url): array
    {
        $query = [];
        $path = preg_replace('#\?.+$#', '', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        return [$path, $query];
    }

    private function buildUrl(string $path, array $query, array $hashes): string
    {
        $hashedQuery = [];
        foreach ($query as $key => $value) {
            $hashedQuery[$hashes[$key]] = $value;
        }

        return "{$path}?" . http_build_query($hashedQuery);
    }

    private function __construct() {}
    private function __wakeup() {}
    private function __clone() {}
}