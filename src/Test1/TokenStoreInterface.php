<?php

namespace RDAlfaGroup\Test1;

interface TokenStoreInterface
{
    public function getTokens(array $hashes): array;
    public function setTokens(array $tokens): array;
}