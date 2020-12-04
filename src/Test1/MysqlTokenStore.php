<?php

namespace RDAlfaGroup\Test1;

class MysqlTokenStore implements TokenStoreInterface
{
    protected \mysqli $_dbal;
    protected array $_config;

    public function __construct(array $config)
    {
        $this->_config = $config;
    }

    public function getTokens(array $hashes): array
    {
        return array_reduce(
            $this->dbal()->query("
                SELECT t.token, h.hash FROM tokens t
                LEFT JOIN token_hashes th ON t.id = th.token_id
                LEFT JOIN hashes h ON th.hash_id = h.id
                WHERE h.hash IN ('" . implode("','", $hashes) . "')
            ")
            ->fetch_all(MYSQLI_ASSOC)
            , static fn (array $tokens, $row) => $tokens + [$row['hash'] => $row['token']],
            []
        );
    }

    public function setTokens(array $tokens): array
    {
        $tokenIDs = $this->insertTokens($tokens);
        $hashes = $this->hash($tokenIDs);
        $hashIDs = $this->insertHashes($hashes);
        $tokenHashes = array_reduce(
            $tokens,
            static function ($tokenHashes, $token) use($hashes, $hashIDs, $tokenIDs) {
                $tokenHashes[] = "({$tokenIDs[$token]},{$hashIDs[$hashes[$token]]})";
                return $tokenHashes;
            },
            []
        );

        $this->insertTokenHashes($tokenHashes);

        return $hashes;
    }

    protected function dbal(): \mysqli
    {
        if (empty($this->_dbal)) {
            $dbal = \mysqli_connect(... array_values($this->_config));
            if (false === $dbal) {
                throw new \RuntimeException(\mysqli_connect_error());
            }
            $this->_dbal = $dbal;
        }

        return $this->_dbal;
    }

    protected function insertTokens(array $tokens): array
    {
        if (
            false === $this->dbal()->query("
                INSERT INTO tokens (token)
                VALUES ('" . implode("'),('", $tokens) . "')
                ON DUPLICATE KEY UPDATE token = token
            ")
        )
            throw new \RuntimeException(\mysqli_error($this->dbal()));

        return $this->getTokenIDs($tokens);
    }

    protected function hash(array $tokenIDs): array
    {
        $hashes = [];

        foreach ($tokenIDs as $token => $tokenID) {
            while ( ! is_null($this->getHashID($hash = bin2hex(random_bytes(4))))) {}
            $hashes[$token] = $hash;
        }

        return $hashes;
    }

    protected function insertHashes(array $hashes): array
    {
        if (
            false === $this->dbal()->query("
                INSERT INTO hashes (hash)
                VALUES ('" . implode("'),('", $hashes) . "')
            ")
        ) {
            throw new \RuntimeException(\mysqli_error($this->dbal()));
        }

        return $this->getHashIDs($hashes);
    }

    protected function insertTokenHashes(array $tokenHashes): void
    {
        if (
            false === $this->dbal()->query("
                INSERT INTO token_hashes
                VALUES " . implode(",", $tokenHashes)
            )
        )
            throw new \RuntimeException(\mysqli_error($this->dbal()));
    }

    protected function getTokenIDs(array $tokens): array
    {
        return $this->getIDs('tokens', 'token', $tokens);
    }

    protected function getHashIDs(array $hashes): array
    {
        return $this->getIDs('hashes', 'hash', $hashes);
    }

    protected function getHashID(string $hash): ?int
    {
        return $this->getIDs('hashes', 'hash', [$hash])[$hash];
    }

    protected function getIDs(string $table, string $key, array $values): array
    {
        return array_reduce(
            $this->dbal()->query("
                SELECT `{$key}`, id FROM {$table}
                WHERE {$key} IN ('" . implode("','", $values) . "')
            ")
            ->fetch_all(MYSQLI_ASSOC),
            static function ($ids, $row) use ($key) {
                return $ids + [$row[$key] => $row['id']];
            },
            []
        );
    }
}